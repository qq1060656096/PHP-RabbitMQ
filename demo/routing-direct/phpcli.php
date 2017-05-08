 <?php
 $data = [
     '0' => 'error',
     '1' => 'warning',
     '2' => 'info',
 ];
for ($i=1 ;$i<=$argv[1];$i++){
    $msg = isset($data[$i%3]) ? $data[$i%3] : '';
    system(" php emit_log_direct.php $msg ");
}
