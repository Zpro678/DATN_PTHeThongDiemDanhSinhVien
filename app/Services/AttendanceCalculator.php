<?php

namespace App\Services;

use App\Models\AttendanceRecord;
use App\Models\ClassMeeting;
use App\Models\MeetingSummary;
use Illuminate\Support\Collection;


class AttendanceCalculator
{
    // -------------------------------------------------------------------
    //  HẰNG SỐ CẤU HÌNH
    // -------------------------------------------------------------------

    /**
     * Mấy hằng số dưới đây là GIÁ TRỊ DỰ PHÒNG của cả hệ thống, không phải luật của lớp.
     *
     * Luật thật của một lớp nằm ở CourseClass::getAttendanceThresholds() — giảng viên tự
     * chỉnh trong trang cài đặt lớp. Chỉ khi không lấy được lớp đó ra (lớp tạo từ lâu chưa
     * có cấu hình, hoặc chỗ code chỉ cầm mỗi con số chứ không cầm object lớp) thì mới rơi
     * về mấy con số này. Nói cách khác: cầm được $class thì luôn hỏi $class, đừng đọc thẳng
     * hằng số ở đây, không là lớp cấu hình riêng bao nhiêu cũng vô nghĩa.
     */

    /** Quỹ vắng mặc định: được vắng 20% tổng số buổi trước khi bị coi là quá phép. */
    public const DEFAULT_ABSENCE_LIMIT_PERCENT = 20.0;

    /** Kêu trước khi chết đuối 5%: chưa chạm mức cấm thi nhưng còn cách 5% là đã nhắc rồi. */
    public const DEFAULT_WARNING_MARGIN_PERCENT = 5.0;

    /** Quỹ vắng chỉ còn từ 2 buổi trở xuống thì bắt đầu báo "sắp hết phép vắng". */
    public const DEFAULT_NEAR_ABSENCE_SESSIONS = 2;

    /** Tụt xuống dưới mức này là nguy cơ CẤM THI. Vắng 20% thì phải học đủ 80%, ra 80. */
    public const MIN_ATTENDANCE_PERCENT = 100 - self::DEFAULT_ABSENCE_LIMIT_PERCENT;

    /** Dưới mức này thì gửi cảnh báo, nhưng chưa cấm thi. Mặc định 80 + 5 = 85%. */
    public const WARNING_PERCENT = self::MIN_ATTENDANCE_PERCENT + self::DEFAULT_WARNING_MARGIN_PERCENT;

    /** Vẫn là quỹ vắng 20% nhưng để sẵn dạng 0.2, khỏi phải chia 100 mỗi lần nhân. */
    public const ABSENCE_LIMIT_RATIO = self::DEFAULT_ABSENCE_LIMIT_PERCENT / 100;

    /**
     * Những trạng thái PHIÊN được tính là "người này có ở lớp".
     *
     * Để ý: đi muộn và có phép cũng nằm trong đây. Vì ở mức phiên ta chỉ hỏi "có mặt hay
     * không" thôi, còn muộn hay có phép thì để bước gộp buổi lo. Đừng nhầm cái này với
     * điểm trừ — có mặt kiểu đi muộn vẫn bị trừ điểm ở bảng DEDUCTIONS bên dưới.
     */
    private const PRESENTISH = ['present', 'late', 'excused'];

    /**
     * Bảng điểm trừ dự phòng, tính theo trạng thái TỔNG KẾT BUỔI. Số dương, càng to càng nặng.
     *
     * Y hệt mấy hằng số ngưỡng ở trên: đây chỉ là cái để rơi về. Bảng thật lấy từ
     * CourseClass::getAttendanceRules() (giảng viên chỉnh ở cột deduct_late/absent/excused)
     * rồi truyền xuống các hàm qua tham số $rules. Mọi hàm dưới đây đều đọc $rules trước,
     * hết cách mới lấy bảng này.
     */
    public const DEDUCTIONS = [
        'present' => 0.0,     // Có mặt  -> không trừ gì cả.
        'late' => 0.5,        // Đi muộn -> mất nửa buổi, đi trễ vẫn hơn không đi.
        'absent' => 1.0,      // Vắng    -> mất trọn một buổi.
        'excused' => 0.0,     // Có phép -> mặc định không trừ, nhưng lớp bật trừ được (xem countedSessions).
    ];

    /** Chữ tiếng Việt để in ra màn hình / file Excel cho từng trạng thái. */
    public const LABELS = [
        'present' => 'Có mặt',
        'late' => 'Đi muộn',
        'absent' => 'Vắng',
        'excused' => 'Có phép',
    ];

    // ===================================================================
    //  NHÓM 1 — LUẬT GỘP BUỔI (chỉ tính toán, không đụng database)
    // ===================================================================

    /**
     * Hỏi cộc lốc: trạng thái phiên này có nghĩa là người đó đang ở lớp không?
     *
     * Chỉ dùng cho trạng thái của MỘT PHIÊN, đừng đem hỏi trạng thái tổng kết buổi.
     * Trả true cho có mặt, đi muộn và có phép — đi muộn thì vẫn tới lớp, có phép thì
     * coi như được tính mặt. Vắng và pending trả false.
     *
     * @param  string  $status  Trạng thái phiên: present|late|absent|excused|pending
     * @return bool  true nếu tính là đang ở lớp.
     */
    public static function isPresent(string $status): bool
    {
        return in_array($status, self::PRESENTISH, true);
    }

    /**
     * Dịch trạng thái thô trong DB thành trạng thái dùng để tính.
     *
     * Thực chất chỉ xử lý mỗi chữ "pending" — nghĩa là hệ thống có tạo sẵn dòng điểm
     * danh cho sinh viên đó nhưng chưa ai chốt gì cả. Gặp pending thì tính VẮNG, không
     * phân biệt phiên QR hay phiên thủ công:
     *  - Phiên QR: không quét mã thì đúng là không có mặt.
     *  - Phiên thủ công: quy ước là giảng viên bấm tên người CÓ MẶT, ai không được bấm
     *    thì hiểu là không có ở lớp. Nếu đổi thành mặc định có mặt thì lớp nào giảng
     *    viên quên điểm danh sẽ thành cả lớp đủ mặt — sai còn nặng hơn.
     * Các trạng thái đã rõ ràng (present/late/absent/excused) thì giữ nguyên, không đụng.
     *
     * @param  string  $status  Trạng thái đang lưu trong attendance_records.
     * @param  bool    $isQr    Phiên này là phiên QR không (qr_token khác null). Hiện chưa
     *                          làm thay đổi kết quả, giữ lại để sau muốn tách luật hai loại
     *                          phiên thì có sẵn chỗ, khỏi phải sửa hết chỗ gọi.
     * @return string  Trạng thái đã dịch xong.
     */
    public static function interpretStatus(string $status, bool $isQr): string
    {
        if ($status === 'pending') {
            // Chưa ai chốt -> tính vắng cho cả hai loại phiên (lý do xem docblock ở trên).
            return 'absent';
        }

        return $status;
    }

    /**
     * Hàm quan trọng nhất nhóm này: nhiều phiên của MỘT sinh viên trong MỘT buổi -> một trạng thái buổi.
     *
     * Ví dụ cho dễ hình dung, buổi đó giảng viên mở 2 phiên (đầu giờ và cuối giờ):
     *   ['absent', 'present']   -> đi muộn (tới trễ, trượt lần điểm danh đầu).
     *   ['present', 'absent']   -> vắng (điểm danh xong rồi bỏ về, không tính là học).
     *   ['present', 'present']  -> có mặt.
     *   ['present', 'excused']  -> có phép (chỉ cần một phiên có phép là phủ cả buổi).
     *
     * LƯU Ý KHI GỌI — sai hai chỗ này là ra kết quả sai mà không báo lỗi:
     *  1. $statuses phải ĐÚNG THỨ TỰ THỜI GIAN, vì hàm phân biệt phiên đầu với phiên cuối.
     *     Query xong nhớ orderBy('id') hoặc ksort trước khi truyền vào.
     *  2. $statuses phải chạy qua interpretStatus() rồi, tức là không còn chữ 'pending' nào.
     *     Còn sót 'pending' thì nó không thuộc PRESENTISH nên bị tính như vắng — tình cờ ra
     *     đúng, nhưng đừng dựa vào đó.
     *
     * @param  array<int, string>  $statuses  Trạng thái từng phiên, đã dịch và đã sắp theo thời gian.
     * @param  array  $rules  Bảng điểm trừ của lớp (getAttendanceRules()); bỏ trống thì dùng DEDUCTIONS.
     * @return array{status: string, deduction: float, label: string}
     */
    public static function consolidateStatuses(array $statuses, array $rules = []): array
    {
        // Buổi không có phiên nào -> không có gì chứng minh sinh viên đi học -> vắng.
        if ($statuses === []) {
            return self::buildResult('absent', $rules);
        }

        // Soi chuỗi trạng thái để ra một chữ: present/late/absent/excused.
        $state = self::classifyPattern($statuses);

        // Kèm thêm điểm trừ và nhãn tiếng Việt cho chỗ gọi khỏi phải tự tra.
        return self::buildResult($state, $rules);
    }

    /**
     * Chỗ đặt luật gộp buổi. Đọc từ trên xuống, gặp điều kiện nào đúng trước thì chốt luôn.
     *
     * Thứ tự các câu if BÊN DƯỚI CHÍNH LÀ THỨ TỰ ƯU TIÊN của luật, đảo chỗ là đổi nghiệp vụ:
     *  1. Thấy 'excused' ở bất kỳ phiên nào -> cả buổi có phép. Đặt trên cùng vì giấy phép
     *     phải thắng mọi thứ khác, sinh viên có phép mà bị ghi vắng là sai.
     *  2. Phiên CUỐI không có mặt -> vắng. Điểm danh đầu giờ rồi về sớm thì không tính học.
     *  3. Phiên ĐẦU không có mặt (mà cuối có) -> đi muộn. Đến trễ nên trượt lần đầu.
     *  4. Có phiên nào giảng viên bấm 'late' -> đi muộn. Tôn trọng thao tác tay của giảng viên.
     *  5. Qua hết -> có mặt đàng hoàng.
     *
     * @param  array<int, string>  $statuses  Trạng thái từng phiên, đã sắp theo thời gian.
     * @return string  Một trong: present|late|absent|excused.
     */
    private static function classifyPattern(array $statuses): string
    {
        // Phòng thân: hàm private nên thường không rơi vào đây, consolidateStatuses chặn trước rồi.
        if ($statuses === []) {
            return 'absent';
        }

        // (1) Có phép thắng tất cả.
        if (in_array('excused', $statuses, true)) {
            return 'excused';
        }

        // Lấy phiên đầu và phiên cuối. Dùng array_key_first/last thay cho [0] và [count-1]
        // để không phụ thuộc mảng có key liên tục từ 0 hay không.
        $firstStatus = (string) $statuses[array_key_first($statuses)];
        $lastStatus = (string) $statuses[array_key_last($statuses)];

        // (2) Cuối giờ không còn ở lớp -> vắng.
        if (! self::isPresent($lastStatus)) {
            return 'absent';
        }

        // (3) Cuối giờ có nhưng đầu giờ không -> tới muộn.
        if (! self::isPresent($firstStatus)) {
            return 'late';
        }

        // (4) Đủ mặt đầu cuối, nhưng giảng viên có bấm muộn ở phiên nào đó thì vẫn là muộn.
        if (in_array('late', $statuses, true)) {
            return 'late';
        }

        // (5) Sạch sẽ.
        return 'present';
    }

    /**
     * Đóng gói một chữ trạng thái thành mảng đầy đủ {status, deduction, label} cho chỗ gọi xài luôn.
     *
     * Có hàm này để mọi đường ra của consolidateStatuses đều cùng một hình dạng — chỗ gọi
     * không phải nhớ tự tra điểm trừ với tự dịch nhãn, và sau này thêm field mới thì sửa
     * đúng một nơi.
     *
     * @param  string  $state  Trạng thái tổng kết buổi (present/late/absent/excused).
     * @param  array   $rules  Bảng điểm trừ của lớp.
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
     * Trạng thái này bị trừ mấy điểm?
     *
     * Tra theo thứ tự: luật riêng của lớp ($rules) trước, không có thì lấy bảng dự phòng
     * DEDUCTIONS, vẫn không có nữa thì trừ 1.0. Cái 1.0 cuối là cố tình chọn nặng: gặp
     * trạng thái lạ (dữ liệu hỏng, ai đó thêm status mới mà quên khai vào bảng) thì thà
     * trừ oan rồi giảng viên sửa tay, còn hơn trừ 0 làm cả lớp tự nhiên đủ chuyên cần
     * mà không ai phát hiện ra.
     *
     * @param  string  $status  Trạng thái tổng kết buổi.
     * @param  array   $rules   Bảng điểm trừ của lớp, lấy từ getAttendanceRules().
     * @return float  Điểm trừ, luôn là số dương.
     */
    public static function deductionForStatus(string $status, array $rules = []): float
    {
        return (float) ($rules[$status] ?? self::DEDUCTIONS[$status] ?? 1.0);
    }

    /**
     * Đổi mã trạng thái sang chữ tiếng Việt để in ra cho người đọc.
     *
     * Trạng thái lạ thì trả 'Vắng' — cùng tinh thần với deductionForStatus: thà hiện
     * nặng hơn thực tế để có người thắc mắc, hơn là hiện nhẹ rồi trôi luôn.
     */
    public static function statusLabel(string $status): string
    {
        return self::LABELS[$status] ?? 'Vắng';
    }

    // ===================================================================
    //  NHÓM 2 — ĐẾM SỐ BUỔI THEO TỪNG TRẠNG THÁI cho một sinh viên
    // ===================================================================

    /**
     * Đưa vào tất cả dòng điểm danh của MỘT sinh viên, nhận về bảng đếm cả kỳ:
     * đi học mấy buổi, muộn mấy buổi, vắng mấy buổi, tổng bị trừ bao nhiêu.
     *
     * Đây là cầu nối giữa nhóm 1 và nhóm 3: nhóm 1 gộp được một buổi, hàm này lặp cho
     * cả kỳ, rồi nhóm 3 lấy bảng đếm đó ra tính phần trăm.
     *
     * Làm ba bước:
     *  1. Xếp các dòng record vào giỏ hai tầng $byMeeting[buổi][phiên] = trạng thái đã dịch.
     *     Dòng nào không biết thuộc buổi nào (meeting_id null) thì bỏ, vì không gộp buổi được.
     *  2. Mỗi buổi: ksort cho các phiên về đúng thứ tự rồi nhờ consolidateStatuses chốt một chữ.
     *  3. Cộng vào bảng đếm, đồng thời cộng dồn điểm trừ.
     *
     * CHỈ TRUYỀN VÀO RECORD CỦA PHIÊN ĐÃ CHỐT. Lôi cả phiên đang mở vào thì sinh viên chưa
     * kịp quét đã bị tính vắng, cuối cùng ra con số chuyên cần thấp giả.
     *
     * @param  iterable  $rows  Mỗi phần tử là object có ->meeting_id và ->status; nên có thêm
     *                          ->class_session_id (hoặc ->session_id) và ->qr_token. Thiếu
     *                          class_session_id thì mọi phiên rơi vào key 0, các phiên trong
     *                          cùng buổi đè lên nhau và luật đầu/cuối hỏng.
     * @param  array  $rules  Bảng điểm trừ của lớp.
     * @return array{present:int, late:int, excused:int, absent:int, total:int, deduction:float}
     *         total = số buổi đã học, deduction = tổng điểm trừ (số lẻ, vì muộn trừ 0.5).
     */
    public static function consolidateByMeeting(iterable $rows, array $rules = []): array
    {
        // (1) Xếp record vào giỏ [buổi][phiên] = trạng thái.
        $byMeeting = [];
        foreach ($rows as $row) {
            $meetingId = $row->meeting_id ?? null;
            if ($meetingId === null) {
                continue; // Không biết thuộc buổi nào thì không gộp được, bỏ qua.
            }

            $sessionId = (int) ($row->class_session_id ?? $row->session_id ?? 0);
            $isQr = ! empty($row->qr_token);
            $status = self::interpretStatus((string) $row->status, $isQr);

            $byMeeting[$meetingId][$sessionId] = $status;
        }

        // Bảng đếm: mỗi trạng thái đếm được bao nhiêu BUỔI (không phải bao nhiêu phiên).
        $counts = ['present' => 0, 'late' => 0, 'excused' => 0, 'absent' => 0, 'total' => 0, 'deduction' => 0.0];

        // (2)+(3) Gộp từng buổi rồi cộng vào bảng đếm.
        foreach ($byMeeting as $sessions) {
            // Sắp theo id phiên, id tăng dần theo thời gian tạo nên coi như thứ tự thời gian.
            // Bỏ dòng này là luật "phiên đầu / phiên cuối" tính sai ngay.
            ksort($sessions);
            $result = self::consolidateStatuses(array_values($sessions), $rules);

            $counts['total']++;
            $counts[$result['status']] = ($counts[$result['status']] ?? 0) + 1;
            $counts['deduction'] += $result['deduction'];
        }

        return $counts;
    }

    // ===================================================================
    //  NHÓM 3 — TỪ BẢNG ĐẾM RA PHẦN TRĂM CHUYÊN CẦN VÀ QUỸ VẮNG
    // ===================================================================

    /**
     * Tính MẪU SỐ: rốt cuộc lấy bao nhiêu buổi ra chia để có phần trăm chuyên cần.
     *
     * Chuyện chỉ xoay quanh buổi vắng có phép, và tuỳ lớp cấu hình có trừ điểm nó hay không:
     *  - Lớp KHÔNG trừ vắng có phép (mặc định): mẫu số giữ nguyên số buổi dự kiến. Buổi có
     *    phép không bị trừ ở tử số nên coi như đi học bình thường.
     *  - Lớp CÓ trừ vắng có phép: bỏ hẳn mấy buổi đó ra khỏi mẫu số. Nghỉ có phép 2 trong 10
     *    buổi thì chỉ chấm trên 8 buổi còn lại, sinh viên không bị thiệt vì cái nghỉ hợp lệ.
     *
     * Có max(..., 0) để phòng trường hợp số buổi có phép lớn hơn số buổi dự kiến (lớp dạy lố
     * kế hoạch) — không cho mẫu số âm, vì âm là phần trăm ra số quái dị.
     *
     * @param  int    $plannedSessions  Tổng số buổi dự kiến của lớp.
     * @param  int    $excusedSessions  Số buổi sinh viên vắng có phép.
     * @param  array  $rules            Bảng điểm trừ của lớp; xem key 'excused' để biết có trừ không.
     * @return int  Số buổi dùng làm mẫu số, không bao giờ âm.
     */
    public static function countedSessions(int $plannedSessions, int $excusedSessions, array $rules = []): int
    {
        $excusedDeduction = (float) ($rules['excused'] ?? self::DEDUCTIONS['excused'] ?? 0.0);

        return $excusedDeduction > 0 ? max($plannedSessions - $excusedSessions, 0) : $plannedSessions;
    }

    /**
     * Chốt "tổng số buổi" để tính quỹ vắng: lấy cái LỚN HƠN giữa số buổi dự kiến và số buổi đã dạy thật.
     *
     * Sinh ra để xử lý lớp dạy lố kế hoạch. Lớp khai 15 buổi nhưng thực tế dạy 18, nếu cứ bám
     * con số 15 thì quỹ vắng vẫn là 3 buổi trong khi sinh viên phải đi học 18 buổi — vô lý và
     * thiệt cho sinh viên. Lấy 18 thì quỹ vắng giãn ra thành 3 buổi (18 × 20% = 3.6, làm tròn
     * xuống). Ngược lại lớp mới dạy được 5/15 buổi thì vẫn lấy 15, để quỹ vắng không co lại
     * còn 1 buổi làm sinh viên mới nghỉ một hôm đã bị doạ cấm thi.
     *
     * @param  int  $plannedSessions  Số buổi dự kiến, lấy từ classes.total_sessions.
     * @param  int  $studiedSessions  Số buổi đã dạy xong, thường là $counts['total'].
     * @return int  Số lớn hơn trong hai cái, và không âm.
     */
    public static function baseSessions(int $plannedSessions, int $studiedSessions): int
    {
        return max($plannedSessions, $studiedSessions, 0);
    }

    /**
     * Sinh viên được phép vắng tối đa mấy buổi thì chưa bị cấm thi.
     *
     * Công thức: số buổi × quỹ vắng phần trăm, rồi LÀM TRÒN XUỐNG. Lớp 15 buổi quỹ 20% thì
     * ra đúng 3. Lớp 12 buổi thì 12 × 20% = 2.4, làm tròn xuống còn 2 — nghiêm hơn, và cố ý
     * như vậy: quy chế thì thà chặt hơn là lỏng.
     *
     * Hai điểm dễ dùng sai:
     *  - Tham số đầu tuy tên là $plannedSessions nhưng phải truyền KẾT QUẢ CỦA baseSessions(),
     *    đừng nhét thẳng classes.total_sessions vào. Nhét thẳng thì lớp dạy lố kế hoạch sẽ bị
     *    tính quỹ vắng thiếu.
     *  - Con số đem so với quỹ vắng này là số buổi vắng QUY ĐỔI (effectiveAbsence), tức là đã
     *    cộng cả đi muộn 0.5 buổi, không phải đếm thô số buổi vắng.
     *
     * Dòng max/min chỉ để kẹp phần trăm về khoảng 0..100, phòng dữ liệu bẩn trong DB (ai đó
     * sửa tay ra số âm hoặc 250%) làm quỹ vắng ra số vô lý.
     *
     * @param  int         $plannedSessions      Số buổi nền, tức kết quả của baseSessions().
     * @param  float|null  $absenceLimitPercent  Quỹ vắng của lớp; null thì lấy mặc định hệ thống (20%).
     * @return int  Số buổi được phép vắng.
     */
    public static function allowedAbsentSessions(int $plannedSessions, ?float $absenceLimitPercent = null): int
    {
        $percent = $absenceLimitPercent ?? self::DEFAULT_ABSENCE_LIMIT_PERCENT;
        $percent = max(0.0, min(100.0, $percent));

        return (int) floor($plannedSessions * $percent / 100);
    }

    /**
     * Cắt đuôi số 0 cho phần trăm khi đem ghép vào câu chữ.
     *
     * Để viết "cần đạt 80%" chứ không phải "cần đạt 80.00%", mà giảng viên có cấu hình lẻ
     * 12.5% thì vẫn ra "12.5". Chỉ dùng cho hiển thị, đừng đem kết quả đi tính tiếp.
     */
    public static function formatPercent(float $percent): string
    {
        return rtrim(rtrim(number_format($percent, 2, '.', ''), '0'), '.');
    }

    /**
     * Cộng hết điểm trừ cả kỳ lại thành một số — chính là "vắng bao nhiêu buổi quy đổi".
     *
     * Đi qua present/late/absent, mỗi trạng thái lấy số buổi nhân với điểm trừ tương ứng.
     * Ví dụ vắng 2 buổi (2 × 1.0) và muộn 3 buổi (3 × 0.5) thì ra 3.5 — đọc là "vắng quy
     * đổi 3.5 buổi". Cứ đi muộn hai lần thì mất bằng nghỉ hẳn một buổi.
     *
     * CỐ Ý BỎ QUA 'excused'. Buổi vắng có phép đã được xử lý ở mẫu số bên countedSessions()
     * rồi, cộng thêm ở đây nữa là trừ hai lần cho cùng một buổi.
     *
     * @param  array<string, int>  $counts  Bảng đếm từ consolidateByMeeting().
     * @param  array  $rules  Bảng điểm trừ của lớp.
     * @return float  Tổng điểm trừ, có phần lẻ vì đi muộn tính 0.5.
     */
    public static function lostFromCounts(array $counts, array $rules = []): float
    {
        $lost = 0.0;
        foreach (['present', 'late', 'absent'] as $state) {
            $lost += (int) ($counts[$state] ?? 0) * (float) ($rules[$state] ?? self::DEDUCTIONS[$state] ?? 1.0);
        }

        return $lost;
    }

    /**
     * Số buổi vắng quy đổi, dùng để đem so với quỹ vắng xem có bị cấm thi chưa.
     *
     * Ruột y hệt lostFromCounts() — cùng một con số, khác mỗi cái tên. Tách ra để chỗ gọi
     * đọc đúng ý mình đang làm gì: bàn về "mất mấy điểm chuyên cần" thì gọi lostFromCounts,
     * bàn về "vắng mấy buổi rồi, còn được nghỉ mấy buổi nữa" thì gọi hàm này. Sau này nếu
     * hai khái niệm tách luật khác nhau thì cũng đã có sẵn chỗ để sửa riêng.
     *
     * Nhớ là kết quả trả float. Đem so với allowedAbsentSessions() (int) thì 2.5 > 2, tức
     * là đã vượt quỹ vắng chứ không phải vừa đủ.
     *
     * @param  array<string, int>  $counts  Bảng đếm từ consolidateByMeeting().
     * @param  array  $rules  Bảng điểm trừ của lớp.
     */
    public static function effectiveAbsence(array $counts, array $rules = []): float
    {
        return self::lostFromCounts($counts, $rules);
    }

    /**
     * Ra con số cuối cùng người dùng nhìn thấy: phần trăm chuyên cần, 0 đến 100.
     *
     * Chạy như sau:
     *  1. Lấy mẫu số từ countedSessions().
     *  2. Mẫu số bằng 0 (lớp chưa khai số buổi, hoặc nghỉ có phép hết cả kỳ) thì trả 100.
     *     Không có gì để chấm thì cho qua, chứ không phải cho 0 rồi doạ cấm thi oan.
     *  3. Tử số = mẫu số trừ đi tổng điểm trừ, kẹp không cho âm.
     *  4. Chia, nhân 100, làm tròn thành số nguyên.
     *
     * Điểm dễ hiểu nhầm: mẫu số là số buổi DỰ KIẾN cả kỳ, không phải số buổi đã dạy. Nên
     * mấy buổi chưa dạy vẫn nằm trong mẫu số mà chưa bị trừ gì, coi như tạm tính có mặt.
     * Vì vậy đầu kỳ ai cũng gần 100%, rồi tụt dần theo số buổi nghỉ — đúng như mong đợi,
     * chứ không phải bug.
     *
     * @param  int  $plannedSessions  Tổng số buổi dự kiến của lớp.
     * @param  array<string, int>  $counts  Bảng đếm từ consolidateByMeeting().
     * @param  array  $rules  Bảng điểm trừ của lớp.
     * @return int  Phần trăm chuyên cần đã làm tròn.
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
     * Phần "đã chuyên cần" của một sinh viên, để dành cộng dồn cả lớp — chưa chia, chưa làm tròn.
     *
     * Dùng khi tính phần trăm trung bình của cả lớp. Nếu lấy percentOfPlanned() của từng
     * người rồi cộng lại chia đầu người thì mỗi người đã bị làm tròn một lần, cộng vài chục
     * người là lệch thấy rõ. Cách đúng: mỗi người lấy attendedWeight() và countedSessions(),
     * cộng riêng hai cột đó cho cả lớp, cuối cùng mới chia một lần duy nhất.
     *
     * @param  int  $countedSessions  Mẫu số của riêng sinh viên đó (đã qua countedSessions()).
     * @param  array<string, int>  $counts  Bảng đếm của sinh viên đó.
     * @param  array  $rules  Bảng điểm trừ của lớp.
     * @return float  Số buổi quy đổi mà sinh viên này thực sự có mặt, không âm.
     */
    public static function attendedWeight(int $countedSessions, array $counts, array $rules = []): float
    {
        return max($countedSessions - self::lostFromCounts($counts, $rules), 0);
    }

    // ===================================================================
    //  NHÓM 4 — CÓ TRUY VẤN DATABASE: dựng bảng tổng kết buổi và ghi xuống DB
    // ===================================================================

    /**
     * Dựng bảng tổng kết của MỘT buổi cho TOÀN BỘ sinh viên đang học — tính xong trả về, KHÔNG ghi DB.
     *
     * Đây là thứ đổ ra màn hình "Tổng kết buổi" và file Excel xuất ra: mỗi dòng một sinh viên,
     * kèm trạng thái từng phiên để hiển thị badge, và trạng thái chốt của cả buổi.
     *
     * Các bước:
     *  1. Đọc bảng điểm trừ của lớp.
     *  2. Lấy các phiên của buổi, sắp theo id để đúng thứ tự thời gian, kèm qr_token.
     *  3. Lấy danh sách sinh viên đang học (bỏ người đã rời lớp), sắp theo tên cho dễ dò.
     *  4. Lấy một lần toàn bộ record điểm danh của mấy phiên đó rồi gom theo sinh viên.
     *  5. Duyệt từng sinh viên, dựng chuỗi trạng thái theo đúng thứ tự phiên rồi gộp thành một chữ.
     *
     * Điểm cần giữ khi sửa hàm này:
     *  - Bước 4 gom sẵn record vào bộ nhớ rồi bước 5 chỉ tra trong đó, cố tình như vậy để tránh
     *    N+1. Đừng chuyển thành query trong vòng lặp, lớp đông sẽ bắn ra hàng trăm câu lệnh.
     *  - Duyệt theo $sessions chứ không duyệt theo record có sẵn. Nhờ vậy sinh viên không có
     *    record ở phiên nào thì phiên đó thành 'pending', rồi interpretStatus quy về vắng. Nếu
     *    chỉ duyệt record đang có thì người vắng cả buổi lại ra mảng rỗng và bị hiểu sai.
     *
     * @return Collection<int, array{member: \App\Models\ClassMember, statuses: array<int,string>, status: string, deduction: float, label: string}>
     */
    public static function consolidateMeeting(ClassMeeting $meeting): Collection
    {
        // (1) Luật điểm trừ do giảng viên cấu hình cho lớp này.
        $rules = $meeting->courseClass->getAttendanceRules();

        // (2) Các phiên của buổi. orderBy('id') để giữ đúng thứ tự thời gian — luật đầu/cuối
        // dựa hết vào đây. Lấy kèm qr_token để biết phiên QR hay phiên thủ công.
        $sessions = $meeting->sessions()->orderBy('id')->get(['id', 'qr_token']);
        $sessionIds = $sessions->pluck('id');

        // (3) Chỉ lấy sinh viên còn đang học; người đã rời lớp không nên nằm trong bảng tổng kết.
        $members = $meeting->courseClass->members()
            ->where('status', \App\Models\ClassMember::STATUS_ACTIVE)
            ->with('profile')
            ->get()
            ->sortBy(fn ($member) => $member->display_name)
            ->values();

        // (4) Một câu query duy nhất cho toàn bộ record, gom sẵn theo sinh viên để bước 5 tra offline.
        $records = AttendanceRecord::query()
            ->whereIn('class_session_id', $sessionIds)
            ->get(['class_member_id', 'class_session_id', 'status'])
            ->groupBy('class_member_id');

        // (5) Từng sinh viên một.
        return $members->map(function ($member) use ($sessions, $records, $rules) {
            // Record của riêng người này, đánh key theo phiên cho dễ tra.
            $bySession = ($records->get($member->id) ?? collect())->keyBy('class_session_id');

            // Duyệt theo danh sách PHIÊN, không duyệt theo record — phiên nào không có record
            // thì mặc định 'pending' rồi để interpretStatus quy về vắng.
            $statuses = $sessions
                ->map(function ($session) use ($bySession) {
                    $raw = $bySession->get($session->id)?->status ?? 'pending';

                    return self::interpretStatus($raw, $session->qr_token !== null);
                })
                ->all();

            $result = self::consolidateStatuses($statuses, $rules);

            return [
                'member' => $member,           // Model ClassMember, để lấy tên và mã sinh viên.
                'statuses' => $statuses,       // Trạng thái từng phiên, để vẽ badge "Lần 1, Lần 2...".
                'status' => $result['status'], // Trạng thái chốt của cả buổi.
                'deduction' => $result['deduction'],
                'label' => $result['label'],
            ];
        })->values();
    }

    /**
     * Tính tổng kết buổi rồi GHI XUỐNG bảng meeting_summaries.
     *
     * consolidateMeeting() chỉ tính rồi trả về, hàm này mới là hàm ghi. Từ lúc ghi xong,
     * bảng meeting_summaries trở thành nguồn dữ liệu chính thức của buổi đó — quan trọng
     * vì lệnh dọn dẹp attendance:cleanup-expired-sessions sẽ xoá sạch phiên và record sau
     * 48 tiếng, lúc đó chỉ còn mấy dòng summary này giữ lại kết quả. Nên PHẢI gọi hàm này
     * trước khi xoá, xoá trước là mất dữ liệu vĩnh viễn.
     *
     * Chỗ tinh tế nhất là cột is_overridden:
     *  - auto_status LUÔN được cập nhật, đây là "hệ thống nghĩ sao". Cứ ghi đè thoải mái.
     *  - status và deduction là "kết quả chính thức", chỉ ghi đè khi giảng viên CHƯA sửa tay.
     *    Sinh viên nộp đơn xin phép muộn, giảng viên sửa 'absent' thành 'excused' và đánh dấu
     *    is_overridden — lần chạy sau hàm này phải để nguyên, không được tính lại rồi đạp lên
     *    quyết định của giảng viên. Bỏ câu if đó đi là mọi chỉnh tay bay sạch sau lần chạy kế.
     * Giữ cả hai cột để trang tổng kết còn đối chiếu được "máy tính ra X, giảng viên sửa thành Y".
     *
     * Cuối cùng bắn event ClassDataUpdated để mấy màn hình đang mở tự cập nhật theo realtime.
     */
    public static function syncSummaries(ClassMeeting $meeting): void
    {
        foreach (self::consolidateMeeting($meeting) as $row) {
            // Có dòng cũ thì lấy ra dùng lại, chưa có thì tạo mới — tránh nhân bản dòng
            // mỗi lần chạy, vì hàm này chạy lại nhiều lần cho cùng một buổi.
            $summary = MeetingSummary::query()->firstOrNew([
                'meeting_id' => $meeting->id,
                'class_member_id' => $row['member']->id,
            ]);

            // Máy tính ra gì thì ghi nấy, kể cả khi giảng viên đã sửa tay.
            $summary->auto_status = $row['status'];

            // Chỉ đụng vào kết quả chính thức khi giảng viên chưa can thiệp.
            if (! $summary->is_overridden) {
                $summary->status = $row['status'];
                $summary->deduction = $row['deduction'];
            }

            $summary->save();
        }

        // Báo cho các màn hình đang mở của lớp này biết mà tải lại số liệu.
        event(new \App\Events\ClassDataUpdated((string) $meeting->class_id));
    }
}
