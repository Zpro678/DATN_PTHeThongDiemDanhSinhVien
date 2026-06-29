<?php

namespace App\Services;

use App\Models\AttendanceRecord;
use App\Models\ClassMeeting;
use App\Models\MeetingSummary;
use Illuminate\Support\Collection;

/**
 * ============================================================================
 *  AttendanceCalculator — NGUỒN DUY NHẤT tính điểm chuyên cần của toàn hệ thống.
 * ============================================================================
 *
 * KHÁI NIỆM:
 *  - PHIÊN (class_session): một lần mở điểm danh (thủ công hoặc QR). Một phiên cho 1 sinh viên
 *    chỉ có 2 khả năng: CÓ MẶT hay VẮNG (nhị phân 1/0).
 *  - BUỔI (class_meeting): một buổi học, gồm nhiều phiên. Đơn vị tính chuyên cần là BUỔI (mỗi buổi = 1 đơn vị).
 *
 * THUẬT TOÁN TỔNG KẾT 1 BUỔI (theo đặc tả "điểm danh.docx", mục 8):
 *  Quy mỗi phiên về 0/1 rồi xét MẪU chuỗi bit để ra 1 trong 6 trạng thái:
 *    present     (Có mặt)         : tất cả phiên đều có mặt.
 *    late        (Đi muộn)        : vắng ở đầu rồi có mặt liền mạch tới cuối (vd 0111, 0011), có mặt ≥ vắng.
 *    partial     (Vắng giữa giờ)  : có mặt nhưng có "lỗ" ở giữa (vd 1101, 1011, 1010).
 *    early_leave (Về sớm)         : có mặt ở đầu rồi nghỉ liền mạch (vd 1110, 1100), có mặt ≥ vắng.
 *    absent      (Vắng)           : các trường hợp còn lại (0000, 0001, 0101, 0010, 1000...).
 *    excused     (Có phép)        : tất cả phiên đều "có phép".
 *
 * DIỄN GIẢI "pending" (record chưa được điểm danh) theo LOẠI phiên:
 *  - Phiên QR  (qr_token != null): sinh viên chưa quét  -> coi như VẮNG (0).
 *  - Phiên thủ công (qr_token null): giảng viên chưa đánh dấu -> mặc định CÓ MẶT (1).
 *
 * ĐIỂM TRỪ & % CHUYÊN CẦN:
 *  - Mỗi trạng thái có 1 "điểm trừ" (số dương) lấy từ bảng tạm self::DEDUCTIONS.
 *  - % chuyên cần = (counted − tổng_điểm_trừ) / counted × 100.
 *    counted = số buổi dự kiến − số buổi vắng-có-phép (nếu lớp bật trừ vắng có phép).
 *
 * GHI CHÚ: file này gộp toàn bộ logic (trước đây tách ở MeetingConsolidationService đã xoá).
 *          Mọi hàm đều `static` (thuần) trừ phần "DB-aware" ở cuối (vẫn static nhưng có truy vấn DB).
 */
class AttendanceCalculator
{
    // -------------------------------------------------------------------
    //  HẰNG SỐ CẤU HÌNH
    // -------------------------------------------------------------------

    /** MIN_ATTENDANCE_PERCENT: dưới ngưỡng % này (80%) -> nguy cơ CẤM THI. */
    public const MIN_ATTENDANCE_PERCENT = 80;

    /** WARNING_PERCENT: dưới ngưỡng % này (85%) nhưng chưa cấm -> CẢNH BÁO chuyên cần. */
    public const WARNING_PERCENT = 85;

    /** ABSENCE_LIMIT_RATIO: quỹ vắng tối đa = 20% tổng số buổi dự kiến của lớp. */
    public const ABSENCE_LIMIT_RATIO = 0.2;

    /** PRESENTISH: các trạng thái PHIÊN được coi là "đã có mặt" khi nhị phân hoá (-> bit 1). */
    private const PRESENTISH = ['present', 'late', 'partial', 'early_leave', 'excused'];

    /**
     * DEDUCTIONS — BẢNG ĐIỂM TRỪ theo trạng thái TỔNG KẾT BUỔI (số dương: càng lớn càng mất nhiều điểm).
     *
     * TODO: Đây là GIÁ TRỊ TẠM (gán biến cứng). Sau này sẽ đọc từ CẤU HÌNH LỚP (đang do người khác làm),
     *       kể cả điểm trừ "vắng có phép". Khi đó chỉ cần thay mảng này bằng giá trị từ class config.
     */
    public const DEDUCTIONS = [
        'present' => 0.0,     // Có mặt              -> không trừ
        'late' => 0.5,        // Đi muộn             -> trừ 0.5
        'partial' => 0.5,     // Vắng giữa giờ       -> trừ 0.5 (đặc tả: 0.5 hoặc 1, tạm 0.5)
        'early_leave' => 1.0, // Về sớm              -> trừ 1
        'absent' => 1.0,      // Vắng                -> trừ 1
        'excused' => 0.0,     // Có phép             -> tạm 0 (cấu hình lớp quyết định sau)
    ];

    /** LABELS — nhãn tiếng Việt để hiển thị cho từng trạng thái tổng kết. */
    public const LABELS = [
        'present' => 'Có mặt',
        'late' => 'Đi muộn',
        'partial' => 'Vắng giữa giờ',
        'early_leave' => 'Về sớm',
        'absent' => 'Vắng',
        'excused' => 'Có phép',
    ];

    // ===================================================================
    //  NHÓM 1 — QUY TẮC TỔNG KẾT (hàm thuần, KHÔNG chạm DB)
    // ===================================================================

    /**
     * isPresent(): một trạng thái PHIÊN có được tính là "có mặt" hay không.
     *
     * @param  string  $status  Trạng thái phiên: present|late|absent|excused|pending...
     * @return bool   true nếu thuộc PRESENTISH (present/late/excused).
     */
    public static function isPresent(string $status): bool
    {
        return in_array($status, self::PRESENTISH, true);
    }

    /**
     * interpretStatus(): DIỄN GIẢI trạng thái "pending" (chưa điểm danh) theo loại phiên.
     *  - Phiên QR  ($isQr = true)  + pending  -> 'absent'  (chưa quét = vắng).
     *  - Phiên thủ công ($isQr=false)+ pending -> 'present' (chưa đánh dấu = mặc định có mặt).
     *  - Các trạng thái khác (present/late/absent/excused) -> GIỮ NGUYÊN.
     *
     * @param  string  $status  Trạng thái gốc của record.
     * @param  bool    $isQr    Phiên này có phải phiên QR không (qr_token != null).
     * @return string  Trạng thái sau diễn giải.
     */
    public static function interpretStatus(string $status, bool $isQr): string
    {
        if ($status === 'pending') {
            return $isQr ? 'absent' : 'present';
        }

        return $status;
    }

    /**
     * consolidateStatuses(): TỔNG KẾT 1 sinh viên trong 1 BUỔI từ chuỗi trạng thái các phiên.
     *
     * Luồng xử lý:
     *   1) Mảng rỗng (không có phiên nào) -> coi như 'absent'.
     *   2) Nếu MỌI phiên đều 'excused' -> 'excused' (có phép cả buổi).
     *   3) Nhị phân hoá: phiên có mặt (present/late/excused) -> 1, vắng -> 0.
     *   4) classifyPattern($bits) -> 1 trong các trạng thái present/late/partial/early_leave/absent.
     *   5) GIỮ "đi muộn" tường minh: nếu mẫu ra 'present' nhưng có phiên đánh 'late' thì hạ thành 'late'.
     *   6) buildResult(): bọc {status, deduction, label}.
     *
     * @param  array<int, string>  $statuses     Trạng thái từng phiên (đã interpretStatus, đã sắp theo thời gian).
     * @param  array               $rules        Mảng luật cấu hình chuyên cần động.
     * @return array{status: string, deduction: float, label: string}
     */
    public static function consolidateStatuses(array $statuses, array $rules = []): array
    {
        // (1) Không có dữ liệu phiên -> vắng.
        if ($statuses === []) {
            return self::buildResult('absent', $rules);
        }

        // (2) Có phép CẢ buổi -> EXCUSED.
        if (collect($statuses)->every(fn (string $s) => $s === 'excused')) {
            return self::buildResult('excused', $rules);
        }

        // (3) $bits: chuỗi nhị phân 0/1 tương ứng từng phiên (1 = có mặt, 0 = vắng).
        $bits = array_map(fn (string $s) => self::isPresent($s) ? 1 : 0, $statuses);

        // (4) Phân loại theo MẪU bit.
        $state = self::classifyPattern($bits);

        // (5) Giữ các trạng thái phạt (nhưng vẫn tính là có mặt) được đánh dấu tường minh:
        if ($state === 'present') {
            if (in_array('partial', $statuses, true)) {
                $state = 'partial';
            } elseif (in_array('early_leave', $statuses, true)) {
                $state = 'early_leave';
            } elseif (in_array('late', $statuses, true)) {
                $state = 'late';
            }
        }

        // (6) Bọc kết quả {status, deduction, label}.
        return self::buildResult($state, $rules);
    }

    /**
     * classifyPattern(): PHÂN LOẠI buổi từ chuỗi nhị phân 0/1 (1 = có mặt, 0 = vắng) — lõi thuật toán mục 8.
     *
     * Biến nội bộ:
     *   $n             = số phiên.
     *   $present       = số phiên có mặt (tổng các bit 1).
     *   $absent        = số phiên vắng (= $n − $present).
     *   $startsPresent = phiên ĐẦU có mặt? (bit[0] == 1)
     *   $endsPresent   = phiên CUỐI có mặt? (bit[n-1] == 1)
     *   $firstP        = chỉ số phiên có mặt ĐẦU TIÊN.
     *   $lastP         = chỉ số phiên có mặt CUỐI CÙNG.
     *   $hasMiddleGap  = có "lỗ" (bit 0) nằm GIỮA $firstP và $lastP không (tức rời lớp giữa chừng).
     *
     * Cây quyết định (khớp 13 ca trong bảng đặc tả):
     *   - all 1            -> present
     *   - all 0            -> absent
     *   - đầu & cuối đều CÓ -> partial (đã có mặt 2 đầu nhưng giữa hụt -> vắng giữa giờ)
     *   - đầu CÓ, cuối VẮNG:
     *        + có lỗ giữa   -> partial   (vd 1010)
     *        + liền mạch    -> early_leave nếu present ≥ absent, else absent (vd 1110/1100 vs 1000)
     *   - đầu VẮNG, cuối CÓ:
     *        + có lỗ giữa   -> absent    (vd 0101 — vắng đầu + đứt quãng -> không tính đi muộn)
     *        + liền mạch    -> late nếu present ≥ absent, else absent (vd 0111/0011 vs 0001)
     *   - đầu & cuối đều VẮNG -> absent
     *
     * @param  array<int, int>  $bits
     * @return string  Một trong: present|late|partial|early_leave|absent.
     */
    private static function classifyPattern(array $bits): string
    {
        $n = count($bits);
        $present = array_sum($bits);
        $absent = $n - $present;

        // Hai trường hợp tuyệt đối.
        if ($present === $n) {
            return 'present';
        }
        if ($present === 0) {
            return 'absent';
        }

        $startsPresent = $bits[0] === 1;
        $endsPresent = $bits[$n - 1] === 1;

        // Tìm phiên có mặt đầu/cuối để dò "lỗ" ở giữa.
        $firstP = array_search(1, $bits, true);       // chỉ số bit 1 đầu tiên
        $lastP = array_keys($bits, 1, true);          // tất cả chỉ số có bit 1
        $lastP = end($lastP);                         // -> chỉ số bit 1 cuối cùng
        $hasMiddleGap = false;
        for ($i = $firstP + 1; $i < $lastP; $i++) {
            if ($bits[$i] === 0) {                     // có 1 phiên vắng nằm giữa hai phiên có mặt
                $hasMiddleGap = true;
                break;
            }
        }

        // Có mặt cả đầu lẫn cuối nhưng (vì không all-1) chắc chắn có lỗ giữa -> vắng giữa giờ.
        if ($startsPresent && $endsPresent) {
            return 'partial';
        }

        // Có mặt đầu, vắng cuối.
        if ($startsPresent && ! $endsPresent) {
            if ($hasMiddleGap) {
                return 'partial';                      // 1010: vào → ra → vào → nghỉ
            }

            return $present >= $absent ? 'early_leave' : 'absent'; // 1110/1100 về sớm; 1000 quá ít -> vắng
        }

        // Vắng đầu, có mặt cuối.
        if (! $startsPresent && $endsPresent) {
            if ($hasMiddleGap) {
                return 'absent';                       // 0101: vắng đầu + đứt quãng -> vắng (không là đi muộn)
            }

            return $present >= $absent ? 'late' : 'absent'; // 0111/0011 đi muộn; 0001 quá ít -> vắng
        }

        // Vắng cả đầu lẫn cuối -> vắng.
        return 'absent';
    }

    /**
     * buildResult(): bọc 1 trạng thái thành cấu trúc chuẩn {status, deduction, label}.
     *
     * @param  string  $state          Trạng thái tổng kết (present/late/partial/early_leave/absent/excused).
     * @param  array   $rules          Cấu hình điểm trừ.
     * @return array{status: string, deduction: float, label: string}
     */
    private static function buildResult(string $state, array $rules = []): array
    {
        return [
            'status' => $state,
            'deduction' => self::deductionForStatus($state, $rules),
            'label' => self::LABELS[$state] ?? 'Vắng',
        ];
    }

    /**
     * deductionForStatus(): ĐIỂM TRỪ của 1 trạng thái (tra bảng DEDUCTIONS).
     *  - 'excused' (có phép): nếu lớp bật trừ -> 1.0; ngược lại lấy giá trị tạm trong DEDUCTIONS (0).
     *  - Trạng thái không có trong bảng -> mặc định trừ 1.0 (coi như vắng).
     *
     * @param  string  $status         Trạng thái tổng kết.
     * @param  array   $rules          Cấu hình điểm trừ.
     * @return float   Điểm trừ (số dương).
     */
    public static function deductionForStatus(string $status, array $rules = []): float
    {
        return (float) ($rules[$status] ?? self::DEDUCTIONS[$status] ?? 1.0);
    }

    /**
     * statusLabel(): nhãn tiếng Việt của 1 trạng thái tổng kết (dùng để hiển thị).
     */
    public static function statusLabel(string $status): string
    {
        return self::LABELS[$status] ?? 'Vắng';
    }

    // ===================================================================
    //  NHÓM 2 — GỘP THEO BUỔI cho 1 sinh viên (phục vụ thống kê % chuyên cần)
    // ===================================================================

    /**
     * consolidateByMeeting(): nhận TẤT CẢ record (của 1 sinh viên, ở các phiên đã chốt) và đếm số buổi
     * theo từng trạng thái tổng kết.
     *
     * Cách hoạt động:
     *   1) Gom record theo $byMeeting[meeting_id][session_id] = trạng-thái-đã-diễn-giải.
     *      - $isQr  = phiên có qr_token không -> để interpretStatus.
     *      - bỏ qua record không có meeting_id.
     *   2) Với mỗi buổi: ksort theo session_id (≈ thứ tự thời gian) rồi consolidateStatuses -> 1 trạng thái.
     *   3) Cộng dồn vào $counts theo trạng thái + tổng điểm trừ.
     *
     * @param  iterable  $rows  Mỗi phần tử (object) cần có: ->meeting_id, ->status, và nên có
     *                          ->class_session_id, ->qr_token. CHỈ truyền record của phiên đã chốt.
     * @param  array     $rules
     * @return array{present:int, late:int, partial:int, early_leave:int, excused:int, absent:int, total:int, deduction:float}
     */
    public static function consolidateByMeeting(iterable $rows, array $rules = []): array
    {
        // (1) Gom record về dạng [buổi][phiên] = trạng thái.
        $byMeeting = [];
        foreach ($rows as $row) {
            $meetingId = $row->meeting_id ?? null;
            if ($meetingId === null) {
                continue; // record không gắn buổi -> bỏ.
            }

            $sessionId = (int) ($row->class_session_id ?? $row->session_id ?? 0);
            $isQr = ! empty($row->qr_token);
            $status = self::interpretStatus((string) $row->status, $isQr);

            $byMeeting[$meetingId][$sessionId] = $status;
        }

        // $counts: bộ đếm số BUỔI theo từng trạng thái + tổng điểm trừ.
        $counts = ['present' => 0, 'late' => 0, 'partial' => 0, 'early_leave' => 0, 'excused' => 0, 'absent' => 0, 'total' => 0, 'deduction' => 0.0];

        // (2)+(3) Tổng kết từng buổi rồi cộng dồn.
        foreach ($byMeeting as $sessions) {
            ksort($sessions); // sắp theo id phiên để xác định đúng "phiên đầu/cuối".
            $result = self::consolidateStatuses(array_values($sessions), $rules);

            $counts['total']++;
            $counts[$result['status']] = ($counts[$result['status']] ?? 0) + 1;
            $counts['deduction'] += $result['deduction'];
        }

        return $counts;
    }

    // ===================================================================
    //  NHÓM 3 — % CHUYÊN CẦN (suy từ điểm trừ)
    // ===================================================================

    /**
     * countedSessions(): MẪU SỐ để tính % — số buổi được đưa vào tính chuyên cần.
     *  - Nếu lớp bật trừ vắng có phép: mẫu số = số buổi dự kiến − số buổi vắng có phép.
     *  - Nếu không: mẫu số = số buổi dự kiến (vắng có phép coi như có mặt).
     *
     * @param  int   $plannedSessions   Tổng số buổi dự kiến của lớp.
     * @param  int   $excusedSessions   Số buổi vắng có phép.
     * @param  array $rules
     */
    public static function countedSessions(int $plannedSessions, int $excusedSessions, array $rules = []): int
    {
        $excusedDeduction = (float) ($rules['excused'] ?? self::DEDUCTIONS['excused'] ?? 0.0);
        return $excusedDeduction > 0 ? max($plannedSessions - $excusedSessions, 0) : $plannedSessions;
    }

    /**
     * allowedAbsentSessions(): số buổi được phép vắng = floor(20% × tổng buổi dự kiến).
     */
    public static function allowedAbsentSessions(int $plannedSessions): int
    {
        return (int) floor($plannedSessions * self::ABSENCE_LIMIT_RATIO);
    }

    /**
     * lostFromCounts(): TỔNG ĐIỂM TRỪ (= số buổi vắng QUY ĐỔI) từ bộ đếm $counts.
     *  Cộng điểm trừ của present/late/partial/early_leave/absent theo bảng DEDUCTIONS; BỎ QUA 'excused'
     *  (vì vắng có phép đã được xử lý ở mẫu số countedSessions).
     *  vd: vắng 1.0 + về sớm 1.0 + đi muộn 0.5 + vắng giữa giờ 0.5.
     *
     * @param  array<string, int>  $counts
     * @param  array               $rules
     */
    public static function lostFromCounts(array $counts, array $rules = []): float
    {
        $lost = 0.0;
        foreach (['present', 'late', 'partial', 'early_leave', 'absent'] as $state) {
            $lost += (int) ($counts[$state] ?? 0) * (float) ($rules[$state] ?? self::DEDUCTIONS[$state] ?? 1.0);
        }

        return $lost;
    }

    /**
     * effectiveAbsence(): số buổi vắng QUY ĐỔI dùng để xét QUỸ VẮNG / CẤM THI (= tổng điểm trừ, bỏ có phép).
     *
     * @param  array<string, int>  $counts
     * @param  array               $rules
     */
    public static function effectiveAbsence(array $counts, array $rules = []): float
    {
        return self::lostFromCounts($counts, $rules);
    }

    /**
     * percentOfPlanned(): % CHUYÊN CẦN trên tổng số buổi dự kiến, suy từ điểm trừ.
     *  - $counted = countedSessions(planned, excused, deduct).  (mẫu số)
     *  - Nếu $counted <= 0 -> trả 100 (chưa có buổi nào để tính).
     *  - $attended = counted − tổng_điểm_trừ.  (buổi chưa diễn ra mặc định coi như có mặt, không trừ)
     *  - % = round(attended / counted × 100).
     *
     * @param  int                  $plannedSessions  Tổng buổi dự kiến của lớp.
     * @param  array<string, int>   $counts           Kết quả consolidateByMeeting (đủ 6 trạng thái).
     * @param  array                $rules
     * @return int   % chuyên cần (0..100).
     */
    public static function percentOfPlanned(int $plannedSessions, array $counts, array $rules = []): int
    {
        $counted = self::countedSessions($plannedSessions, (int) ($counts['excused'] ?? 0), $rules);

        if ($counted <= 0) {
            return 100;
        }

        $attended = max($counted - self::lostFromCounts($counts, $rules), 0);

        return (int) round(($attended / $counted) * 100);
    }

    /**
     * attendedWeight(): "trọng số" buổi đã chuyên cần của 1 sinh viên = counted − tổng_điểm_trừ.
     *  Dùng khi cần CỘNG DỒN nhiều sinh viên để ra % trung bình lớp (tránh làm tròn từng người).
     *
     * @param  int                 $countedSessions  Mẫu số (đã trừ vắng có phép nếu cần) của sinh viên.
     * @param  array<string, int>  $counts
     * @param  array               $rules
     */
    public static function attendedWeight(int $countedSessions, array $counts, array $rules = []): float
    {
        return max($countedSessions - self::lostFromCounts($counts, $rules), 0);
    }

    // ===================================================================
    //  NHÓM 4 — TỔNG KẾT BUỔI (DB-aware) — phục vụ trang "Tổng kết" & file xuất
    // ===================================================================

    /**
     * consolidateMeeting(): tính tổng kết cho TẤT CẢ thành viên đang hoạt động của 1 buổi (KHÔNG ghi DB).
     *
     * Cách hoạt động:
     *   1) $deductExcused: đọc cấu hình lớp (deduct_excused_absence).
     *   2) $sessions: các phiên của buổi, sắp theo id (≈ thời gian) — kèm qr_token để diễn giải pending.
     *   3) $members: thành viên active của lớp, sắp theo tên.
     *   4) $records: nạp record điểm danh của các phiên này, gom theo class_member_id.
     *   5) Với mỗi sinh viên: dựng chuỗi trạng thái từng phiên (interpretStatus theo qr_token) rồi
     *      consolidateStatuses -> 1 trạng thái. Trả về kèm 'statuses' (để hiển thị từng phiên).
     *
     * @return Collection<int, array{member: \App\Models\ClassMember, statuses: array<int,string>, status: string, deduction: float, label: string}>
     */
    public static function consolidateMeeting(ClassMeeting $meeting): Collection
    {
        $rules = $meeting->courseClass->getAttendanceRules();

        // Các phiên của buổi (theo id) + qr_token để biết phiên QR hay thủ công.
        $sessions = $meeting->sessions()->orderBy('id')->get(['id', 'qr_token']);
        $sessionIds = $sessions->pluck('id');

        // Thành viên đang học của lớp.
        $members = $meeting->courseClass->members()
            ->where('status', 'active')
            ->orderBy('full_name')
            ->get();

        // Record điểm danh của tất cả phiên này, gom theo từng sinh viên.
        $records = AttendanceRecord::query()
            ->whereIn('class_session_id', $sessionIds)
            ->get(['class_member_id', 'class_session_id', 'status'])
            ->groupBy('class_member_id');

        return $members->map(function ($member) use ($sessions, $records, $rules) {
            // $bySession: record của sinh viên này, tra nhanh theo class_session_id.
            $bySession = ($records->get($member->id) ?? collect())->keyBy('class_session_id');

            // $statuses: chuỗi trạng thái từng phiên (đã diễn giải pending theo loại phiên).
            $statuses = $sessions
                ->map(function ($session) use ($bySession) {
                    $raw = $bySession->get($session->id)?->status ?? 'pending';

                    return self::interpretStatus($raw, $session->qr_token !== null);
                })
                ->all();

            $result = self::consolidateStatuses($statuses, $rules);

            return [
                'member' => $member,          // model ClassMember
                'statuses' => $statuses,      // trạng thái từng phiên (để hiển thị badge từng Lần)
                'status' => $result['status'],// trạng thái tổng kết buổi
                'deduction' => $result['deduction'],
                'label' => $result['label'],
            ];
        })->values();
    }

    /**
     * syncSummaries(): GHI/ĐỒNG BỘ kết quả tổng kết vào bảng meeting_summaries.
     *  - Với mỗi sinh viên: lấy (hoặc tạo mới) dòng summary theo (meeting_id, class_member_id).
     *  - Luôn cập nhật $summary->auto_status (trạng thái hệ thống tự tính, để đối chiếu).
     *  - CHỈ ghi đè status/deduction nếu dòng đó CHƯA bị giảng viên chỉnh tay (is_overridden = false).
     *    -> giữ nguyên chỉnh sửa thủ công của giảng viên.
     */
    public static function syncSummaries(ClassMeeting $meeting): void
    {
        foreach (self::consolidateMeeting($meeting) as $row) {
            $summary = MeetingSummary::query()->firstOrNew([
                'meeting_id' => $meeting->id,
                'class_member_id' => $row['member']->id,
            ]);

            $summary->auto_status = $row['status'];

            if (! $summary->is_overridden) {
                $summary->status = $row['status'];
                $summary->deduction = $row['deduction'];
            }

            $summary->save();
        }
    }
}
