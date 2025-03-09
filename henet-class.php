<?php
class HENET {
    const URL = 'https://dns.he.net/';
    const USERNAME = '';
    const PASSWORD = '';
    const COOKIE_FILE = '/dev/shm/henet-cookiejar.txt';
            
    public function __construct() {
        if (file_exists(self::COOKIE_FILE) && (time()-filemtime(self::COOKIE_FILE))<(3600*12)) // cookie valid for 12H
            $login_process=false; // login credential is valid, no need to login
        else
            $login_process=true;
        if ($login_process) {
            $this->curl_request(self::URL, 'GET', '', self::COOKIE_FILE); // get initial cookie
            $post_data = 'email='.self::USERNAME.'&pass='.self::PASSWORD; // login
            $response = $this->curl_request(self::URL, 'POST', $post_data, self::COOKIE_FILE);
        } else {
            $response = $this->curl_request(self::URL, 'GET', '', self::COOKIE_FILE);
        }
        preg_match_all('/name=.([0-9a-z\.-]+). value=.([0-9]+). src=.*delete.png/', $response, $match);
        $n=count($match[0]);
        for ($i=0;$i<$n;$i++) {
            $name=$match[1][$i];
            $zoneid=$match[2][$i];
            $this->name_to_zoneid[$name]=$zoneid;
        }
    }
    
    public function curl_request($url, $method = 'GET', $data = '', $cookie_file = null) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        if ($cookie_file) {
            curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
            curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
        }
        if ($method === 'POST' && $data) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

    public function add_record($zone, $name, $type, $content, $ttl=3600) {
        $zoneid=$this->name_to_zoneid[$zone];
        if ($zoneid) {
            $post_data="account=&menu=edit_zone&Type=$type&hosted_dns_zoneid=$zoneid&hosted_dns_recordid=&hosted_dns_editzone=1&Priority=&Name=$name&Content=$content&TTL=$ttl&hosted_dns_editrecord=Submit";
            $this->curl_request(self::URL, 'POST', $post_data, self::COOKIE_FILE);
            return true;
        } else {
            echo "zoneid not found\n";
            return false;
        }
    }
    
    public function delete_record($zone, $name, $type) {
        $zoneid=$this->name_to_zoneid[$zone];
        if ($zoneid) {
            $record=$name.'.'.$zone;
            $recordid=$this->get_recordid($record, $type);
            if ($recordid) {
                echo "recordid $recordid\n";
            } else {
                echo "recordid not found\n";
            }
            $post_data="menu=edit_zone&hosted_dns_zoneid=$zoneid&hosted_dns_recordid=$recordid&hosted_dns_editzone=1&hosted_dns_delrecord=1&hosted_dns_delconfirm=delete";
            $this->curl_request(self::URL.'index.cgi', 'POST', $post_data, self::COOKIE_FILE);
        } else {
            echo "zoneid not found\n";
            return false;
        }
    }
    
    public function get_record_type_to_recordid($zone) {
        $zoneid=$this->name_to_zoneid[$zone];
        if ($zoneid) {
            $cont=$this->curl_request(self::URL."?hosted_dns_zoneid=$zoneid&menu=edit_zone&hosted_dns_editzone", 'GET', '', self::COOKIE_FILE);
            file_put_contents('/dev/shm/he.html',$cont);
            preg_match_all('/deleteRecord\(\'([0-9]+)\',\'([_0-9a-z \"\.-]+)\',\'([A-Z]+)/', $cont, $match);
            $n=count($match[0]);
            for ($i=0;$i<$n;$i++) {
                $recordid=$match[1][$i];
                $record=$match[2][$i];
                $type=$match[3][$i];
                $this->record_type_to_recordid[$record][$type]=$recordid;
            }
        } else {
            echo "zoneid not found\n";
        }
    }
    
    public function get_recordid($record,$type='A') {
        if (substr_count($record,'.')==1)
            $zone=$record;
        else
            $zone=ltrim(strstr($record,'.'),'.');
        $this->get_record_type_to_recordid($zone);
        return $this->record_type_to_recordid[$record][$type];
    }
    
    public function list_zoneid() {
        print_r($this->name_to_zoneid);
    }

    public function list_recordid($zone) {
        $this->get_record_type_to_recordid($zone);
        print_r($this->record_type_to_recordid);
    }  
}
?>