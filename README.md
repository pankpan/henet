# henet
PHP class for HE.net DNS management

    include("henet-class.php"); // fill your login info 1st  
    $hn = new HENET();  
    $hn->add_record('yourdomain.com', 'abc', 'A', '10.10.10.10'); // add abc.yourdomain.com A 10.10.10.10  
    $hn->add_record('yourdomain.com', 'abc', 'A'); // delete abc.yourdomain.com A record
