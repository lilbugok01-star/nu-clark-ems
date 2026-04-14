<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

$tablesArray = DB::select('SHOW TABLES');
$databaseName = DB::connection()->getDatabaseName();
$columnKey = 'Tables_in_' . $databaseName;

$tables = [];
foreach($tablesArray as $tableObj) {
    // dynamically get the table name value from the object
    $vals = array_values((array)$tableObj);
    $tables[] = $vals[0];
}

foreach($tables as $table) {
    if ($table == 'migrations' || $table == 'x_schema_migrations') continue;
    $columns = Schema::getColumnListing($table);
    $totalRows = DB::table($table)->count();
    if ($totalRows == 0) continue;
    
    echo "\nTable: $table (Total: $totalRows rows)\n";
    foreach($columns as $col) {
        $nullCount = DB::table($table)->whereNull($col)->count();
        if ($nullCount > 0) {
            $percent = round(($nullCount/$totalRows)*100);
            echo "  - $col: $nullCount nulls ($percent%)\n";
        }
    }
}
echo "\nScan complete.\n";
