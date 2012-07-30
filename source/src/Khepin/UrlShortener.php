<?php
// ---------------------------------------------------------------------------------- 
// Microsoft Developer & Platform Evangelism 
//  
// Copyright (c) Microsoft Corporation. All rights reserved. 
//  
// THIS CODE AND INFORMATION ARE PROVIDED "AS IS" WITHOUT WARRANTY OF ANY KIND,  
// EITHER EXPRESSED OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE IMPLIED WARRANTIES  
// OF MERCHANTABILITY AND/OR FITNESS FOR A PARTICULAR PURPOSE. 
// ---------------------------------------------------------------------------------- 
// The example companies, organizations, products, domain names, 
// e-mail addresses, logos, people, places, and events depicted 
// herein are fictitious.  No association with any real company, 
// organization, product, domain name, email address, logo, person, 
// places, or events is intended or should be inferred. 
// ---------------------------------------------------------------------------------- 


namespace Khepin;

class UrlShortener {

    private $url_list = array();

    private $url_file = '';

    //Local
    private $db_server = 'localhost';
    private $db_user   = 'phptestuser';
    private $db_password = 'phptestuser';
    private $db_name     = 'shorty';
    

    //Used to make sure regex's are valid
    const url_regex = '^(ht|f)tp(s?)\:\/\/[0-9a-zA-Z]([-.\w]*[0-9a-zA-Z])*(:(0-9)*)*(\/?)([a-zA-Z0-9\-\.\?\,\'\/\\\+&amp;%\$#_]*)?^';

    /** Loads the URLs from the DB */
    public function __construct($url_file_name) {
        $this->url_file = $url_file_name;

        $db_url_list = array();

        $con = mysql_connect($this->db_server,$this->db_user,$this->db_password);
        if (!$con)
          {
          die('Could not connect: ' . mysql_error());
          }

        mysql_select_db($this->db_name, $con);

        $result = mysql_query("SELECT Name, Url FROM Url");

        while($row = mysql_fetch_array($result))
          {
            $this->url_list[$row['Name']] = $row['Url'];
          }

        mysql_close($con);
        return $this->url_list;
    }

    /** Gets a sepcfici URL slug */
    public function get($url_slug) {
        return $this->url_list[$url_slug];
    }

    /** Checks to see if a specific slug exists */
    public function exists($url_slug) {
        return isset($this->url_list[$url_slug]);
    }

    /** Adds a new SLUG to the DB (and file) */
    public function add($url_slug, $url) {
        if (!\preg_match(self::url_regex, $url)) {
            throw new \Exception('Invalid url');
        }
        if (isset($this->url_list[$url_slug])) {
            throw new \Exception('Url short name already exists');
        }
        $this->url_list[$url_slug] = $url;
        //comment this out to remove file writing
        $this->dump();
        //Add to DB
        $con = mysql_connect($this->db_server,$this->db_user,$this->db_password);
        if (!$con)
          {
          die('Could not connect: ' . mysql_error());
          }

        mysql_select_db($this->db_name, $con);

        $sqlInsert = "INSERT into URL (Name, Url) values ('$url_slug', '$url')";        

        if (!mysql_query($sqlInsert,$con))
          {
          die('Error: ' . mysql_error());
          }

        mysql_close($con);
    }

    /** Dumps all URLs to the file */
    private function dump() {
        $fh = fopen($this->url_file, 'w');
        foreach ($this->url_list as $url_slug => $url) {
            fwrite($fh, $url_slug . ' = ' . $url . "\n");
        }
        fclose($fh);
    }

    /** Returns all URLs */
    public function getAll(){
        return $this->url_list;
    }
}
