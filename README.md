# henet
PHP class for HE.net DNS management

    // update USERNAME, PASSWORD with your login info
    include("henet-class.php");

    $hn = new HENET();  
    
    // add abc.yourdomain.com A 10.10.10.10  
    $hn->add_record('yourdomain.com', 'abc', 'A', '10.10.10.10');

    // delete abc.yourdomain.com A record
    $hn->delete_record('yourdomain.com', 'abc', 'A');

    // modify abc.yourdomain.com A record with TTL 600
    $hn->modify_record('yourdomain.com', 'abc', 'A', '10.10.10.11', 600);
