<?php
foreach (App\Models\Plan::all() as $p) {
    echo $p->code." | ".$p->name." | ".number_format($p->price)." | dur=".$p->duration_days." | maxCls=".$p->max_classes." | maxSv=".$p->max_students_per_class." | gps=".$p->max_gps_radius." | excel=".($p->can_export_excel?1:0)." | api=".($p->api_access?1:0)." | support=".$p->support_level."\n";
    echo "   features=".json_encode($p->features, JSON_UNESCAPED_UNICODE)."\n";
}
