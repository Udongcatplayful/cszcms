<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Csz_sitemap extends CI_Model {
    
    function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->helper('file');
    }
    
    public function runSitemap() {
        $this->genSitemapXML();
        $this->genSitemapHTML();
        $this->genSitemapROR();
        $this->genSitemapTXT();
        $this->genRobotTXT();
    }
    
    public function getFileTime() {
        /* filemtime — Gets file modification time */
        $xmlfile = FCPATH."sitemap.xml";
        if (file_exists($xmlfile)) {
            return date("F d Y H:i:s.", filemtime($xmlfile));
        }else{
            return FALSE;
        }
    }
    
    private function genRobotTXT() {
        /* Sitemap Generator for robots.txt */
        $robots_txt = '# robots.txt generated by CSZ CMS'."\n";
        $robots_txt.= 'User-agent: *'."\n";
        $robots_txt.= 'Disallow: /admin/'."\n";
        $robots_txt.= 'Disallow: /install/'."\n";
        $robots_txt.= 'Sitemap: '.BASE_URL.'/sitemap.xml'."\n";
        if($robots_txt){
            $file_path = FCPATH."robots.txt";
            $fopen = fopen($file_path, 'wb') or die("can't open file");
            fwrite($fopen, $robots_txt);
            fclose($fopen);
	}
    }
    
    private function genSitemapXML() {
        /* Sitemap Generator for XML */
        $sitemap_xml = '<?xml version="1.0" encoding="UTF-8"?>
        <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" xmlns:video="http://www.google.com/schemas/sitemap-video/1.1">
        <!-- created by CSZ CMS Sitemap Generator www.cszcms.com -->'."\n";
        $sitemap_xml.= '<url>
	<loc>'.BASE_URL.'</loc>
	<changefreq>always</changefreq>
        </url>'."\n";
        $lang = $this->Csz_model->getValueArray('lang_iso', 'lang_iso', 'active', 1, 0, 'lang_iso_id', 'ASC');
        if($lang !== FALSE){
            foreach ($lang as $row) {
                $sitemap_xml.= '<url>
                <loc>'.BASE_URL.'/lang/'.$row['lang_iso'].'</loc>
                <changefreq>always</changefreq>
                </url>'."\n";
            }
        }
        $page = $this->Csz_model->getValueArray('page_url', 'pages', 'active', 1, 0, 'page_url', 'ASC');
        if($page !== FALSE){
            foreach ($page as $row) {
                $sitemap_xml.= '<url>
                <loc>'.BASE_URL.'/'.$row['page_url'].'</loc>
                <changefreq>always</changefreq>
                </url>'."\n";
            }
        }
        $menu_other = $this->Csz_model->getValueArray('*', 'page_menu', "active = '1' AND pages_id = '0' AND drop_menu != '1'", '', 0, 'menu_name', 'ASC');
        if($menu_other !== FALSE){
            foreach ($menu_other as $row) {
                $chkotherlink = strpos($row['other_link'], BASE_URL);
                if($row['other_link'] && $chkotherlink !== FALSE){
                    $sitemap_xml.= '<url>
                    <loc>'.$row['other_link'].'</loc>
                    <changefreq>always</changefreq>
                    </url>'."\n";
                }else if($row['plugin_menu']){
                    $sitemap_xml.= '<url>
                    <loc>'.BASE_URL.'/plugin/'.$row['plugin_menu'].'</loc>
                    <changefreq>always</changefreq>
                    </url>'."\n";
                }
            }
        }
        $plugin = $this->Csz_model->getValueArray('plugin_db_table,plugin_urlrewrite', 'plugin_manager', "plugin_active = '1' AND plugin_db_table != ''", '', 0, 'timestamp_update', 'DESC');
        if($plugin !== FALSE){
            foreach ($plugin as $row) {
                $plugin_db = explode(',', $row['plugin_db_table']); /* Get First table from this field */
                $plugindb = $this->Csz_model->getValueArray('*', $plugin_db[0], "active = '1' AND url_rewrite != ''", '', 0, 'timestamp_update', 'DESC');
                if($plugindb !== FALSE){
                    foreach ($plugindb as $rs) {
                        $sitemap_xml.= '<url>
                        <loc>'.BASE_URL.'/plugin/'.$row['plugin_urlrewrite'].'/view/'.$rs[$plugin_db[0].'_id'].'/'.$rs['url_rewrite'].'</loc>
                        <changefreq>always</changefreq>
                        </url>'."\n";
                    }
                }
            }       
        }
        $sitemap_xml.= '</urlset>'."\n";
        if($sitemap_xml){
            $file_path = FCPATH."sitemap.xml";
            $fopen = fopen($file_path, 'wb') or die("can't open file");
            fwrite($fopen, $sitemap_xml);
            fclose($fopen);
	}
    }
    
    private function genSitemapROR() {
        $webconfig = $this->Csz_admin_model->load_config();
        /* Sitemap Generator for ROR.XML */
        $ror_xml = '<?xml version="1.0" encoding="UTF-8"?>
        <rss version="2.0" xmlns:ror="http://rorweb.com/0.1/">
        <channel>
        <title>ROR Sitemap for '.BASE_URL.'</title>
        <link>'.BASE_URL.'</link>'."\n";
        $ror_xml.= '<item>
	<link>'.BASE_URL.'</link>
	<title>'.$webconfig->site_name.' | '.$webconfig->keywords.'</title>
	<description>'.$webconfig->site_name.' | '.$webconfig->keywords.'</description>
	<ror:updatePeriod>always</ror:updatePeriod>
	<ror:sortOrder>0</ror:sortOrder>
	<ror:resourceOf>sitemap</ror:resourceOf>
        </item>'."\n";
        $page = $this->Csz_model->getValueArray('*', 'pages', 'active', 1, 0, 'page_url', 'ASC');
        if($page !== FALSE){
            $i = 0;
            foreach ($page as $row) {
                $i++;
                if($i < 10) $order = 1;
		else if($i < 50) $order = 2;
		else $order = 3;
                $ror_xml.= '<item>
                        <link>'.BASE_URL.'/'.$row['page_url'].'</link>
                        <title>'.$row['page_name'].'</title>
                        <description>'.$row['page_desc'].'</description>
                        <ror:updatePeriod>always</ror:updatePeriod>
                        <ror:sortOrder>'.$order.'</ror:sortOrder>
                        <ror:resourceOf>sitemap</ror:resourceOf>
                </item>'."\n";
            }
        }
        $menu_other = $this->Csz_model->getValueArray('*', 'page_menu', "active = '1' AND pages_id = '0' AND drop_menu != '1'", '', 0, 'menu_name', 'ASC');
        if($menu_other !== FALSE){
            $i = 0;
            foreach ($menu_other as $row) {
                $i++;
                if($i < 10) $order = 1;
		else if($i < 50) $order = 2;
		else $order = 3;
                $chkotherlink = strpos($row['other_link'], BASE_URL);
                if($row['other_link'] && $chkotherlink !== FALSE){
                    $ror_xml.= '<item>
                        <link>'.$row['other_link'].'</link>
                        <title>'.$row['menu_name'].'</title>
                        <description>'.$row['menu_name'].'</description>
                        <ror:updatePeriod>always</ror:updatePeriod>
                        <ror:sortOrder>'.$order.'</ror:sortOrder>
                        <ror:resourceOf>sitemap</ror:resourceOf>
                    </item>'."\n";
                }else if($row['plugin_menu']){
                    $ror_xml.= '<item>
                        <link>'.BASE_URL.'/plugin/'.$row['plugin_menu'].'</link>
                        <title>'.$row['menu_name'].'</title>
                        <description>'.$row['menu_name'].'</description>
                        <ror:updatePeriod>always</ror:updatePeriod>
                        <ror:sortOrder>'.$order.'</ror:sortOrder>
                        <ror:resourceOf>sitemap</ror:resourceOf>
                    </item>'."\n";
                }
            }
        }
        $plugin = $this->Csz_model->getValueArray('plugin_db_table,plugin_urlrewrite', 'plugin_manager', "plugin_active = '1' AND plugin_db_table != ''", '', 0, 'timestamp_update', 'DESC');
        if($plugin !== FALSE){
            foreach ($plugin as $row) {
                $plugin_db = explode(',', $row['plugin_db_table']); /* Get First table from this field */
                $plugindb = $this->Csz_model->getValueArray('*', $plugin_db[0], "active = '1' AND url_rewrite != ''", '', 0, 'timestamp_update', 'DESC');
                if($plugindb !== FALSE){
                    $i = 0;
                    foreach ($plugindb as $rs) {
                        $i++;
                        if($i < 10) $order = 1;
                        else if($i < 50) $order = 2;
                        else $order = 3;
                        $ror_xml.= '<item>
                                <link>'.BASE_URL.'/plugin/'.$row['plugin_urlrewrite'].'/view/'.$rs[$plugin_db[0].'_id'].'/'.$rs['url_rewrite'].'</link>
                                <title>'.str_replace('-', ' ', $rs['url_rewrite']).'</title>
                                <description>'.$rs['short_desc'].'</description>
                                <ror:updatePeriod>always</ror:updatePeriod>
                                <ror:sortOrder>'.$order.'</ror:sortOrder>
                                <ror:resourceOf>sitemap</ror:resourceOf>
                        </item>'."\n";
                    }
                }
            }
        }
        $ror_xml.= '</channel></rss>'."\n";
        if($ror_xml){
            $file_path = FCPATH."ror.xml";
            $fopen = fopen($file_path, 'wb') or die("can't open file");
            fwrite($fopen, $ror_xml);
            fclose($fopen);
	}
    }
    
    private function genSitemapTXT() {
        /* Sitemap Generator for TXT */
        $sitemap_txt = BASE_URL.''."\n";
        
        $lang = $this->Csz_model->getValueArray('lang_iso', 'lang_iso', 'active', 1, 0, 'lang_iso_id', 'ASC');
        if($lang !== FALSE){
            foreach ($lang as $row) {
                $sitemap_txt.= BASE_URL.'/lang/'.$row['lang_iso'].''."\n";
            }
        }
        $page = $this->Csz_model->getValueArray('page_url', 'pages', 'active', 1, 0, 'page_url', 'ASC');
        if($page !== FALSE){
            foreach ($page as $row) {
                $sitemap_txt.= BASE_URL.'/'.$row['page_url'].''."\n";
            }
        }
        $menu_other = $this->Csz_model->getValueArray('*', 'page_menu', "active = '1' AND pages_id = '0' AND drop_menu != '1'", '', 0, 'menu_name', 'ASC');
        if($menu_other !== FALSE){
            foreach ($menu_other as $row) {
                $chkotherlink = strpos($row['other_link'], BASE_URL);
                if($row['other_link'] && $chkotherlink !== FALSE){
                    $sitemap_txt.= $row['other_link'].''."\n";
                }else if($row['plugin_menu']){
                    $sitemap_txt.= BASE_URL.'/plugin/'.$row['plugin_menu'].''."\n";
                }
            }
        }
        $plugin = $this->Csz_model->getValueArray('plugin_db_table,plugin_urlrewrite', 'plugin_manager', "plugin_active = '1' AND plugin_db_table != ''", '', 0, 'timestamp_update', 'DESC');
        if($plugin !== FALSE){
            foreach ($plugin as $row) {
                $plugin_db = explode(',', $row['plugin_db_table']); /* Get First table from this field */
                $plugindb = $this->Csz_model->getValueArray('*', $plugin_db[0], "active = '1' AND url_rewrite != ''", '', 0, 'timestamp_update', 'DESC');
                if($plugindb !== FALSE){
                    foreach ($plugindb as $rs) {
                        $sitemap_txt.= BASE_URL.'/plugin/'.$row['plugin_urlrewrite'].'/view/'.$rs[$plugin_db[0].'_id'].'/'.$rs['url_rewrite'].''."\n";
                    }
                }
            }
        }
        if($sitemap_txt){
            $file_path = FCPATH."urllist.txt";
            $fopen = fopen($file_path, 'wb') or die("can't open file");
            fwrite($fopen, $sitemap_txt);
            fclose($fopen);
	}
    }
    
    private function genSitemapHTML() {
        $webconfig = $this->Csz_admin_model->load_config();
        $sitemap_html = '<!DOCTYPE html>
        <html>
        <head>
        <title>HTML Site Map - Generated by CSZ CMS</title>
        <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
        <meta name="viewport" content="width=device-width" />
        </head>
        <body>
        <div>
        <h1>HTML Site Map by CSZ CMS</h1>
        <p><b>Last updated: </b><em>'.date("d F Y H:i:s").'</em></p>
        <table cellpadding="0" cellspacing="0" border="0" width="100%">
        <tr valign="top">
        <td class="lpart" colspan="100">';
        $sitemap_html.= '<h2><a href="'.BASE_URL.'" title="'.$webconfig->site_name.' | '.$webconfig->keywords.'">'.$webconfig->site_name.' | '.$webconfig->keywords.'</a></h2>';
        $sitemap_html.= '<h3>Pages List</h3>';
        $page = $this->Csz_model->getValueArray('*', 'pages', 'active', 1, 0, 'page_url', 'ASC');
        if($page !== FALSE){
            foreach ($page as $row) {
                $sitemap_html.= '<h4> - <a href="'.BASE_URL.'/'.$row['page_url'].'" title="'.$row['page_name'].'">'.$row['page_name'].'</a></h4>';
            }
        }
        $sitemap_html.= '<h3>Navigations List</h3>';
        $menu_other = $this->Csz_model->getValueArray('*', 'page_menu', "active = '1' AND pages_id = '0' AND drop_menu != '1'", '', 0, 'menu_name', 'ASC');
        if($menu_other !== FALSE){
            foreach ($menu_other as $row) {
                $chkotherlink = strpos($row['other_link'], BASE_URL);
                if($row['other_link'] && $chkotherlink !== FALSE){
                    $sitemap_html.= '<h4> - <a href="'.$row['other_link'].'" title="'.$row['menu_name'].'">'.$row['menu_name'].'</a></h4>';
                }else if($row['plugin_menu']){
                    $sitemap_html.= '<h4> - <a href="'.BASE_URL.'/plugin/'.$row['plugin_menu'].'" title="'.$row['menu_name'].'">'.$row['menu_name'].'</a></h4>';
                }
            }
        }
        $plugin = $this->Csz_model->getValueArray('plugin_db_table,plugin_urlrewrite', 'plugin_manager', "plugin_active = '1' AND plugin_db_table != ''", '', 0, 'timestamp_update', 'DESC');
        if($plugin !== FALSE){
            foreach ($plugin as $row) {
                $sitemap_html.= '<h3>'.$row['plugin_urlrewrite'].'</h3>';
                $plugin_db = explode(',', $row['plugin_db_table']); /* Get First table from this field */
                $plugindb = $this->Csz_model->getValueArray('*', $plugin_db[0], "active = '1' AND url_rewrite != ''", '', 0, 'timestamp_update', 'DESC');
                if($plugindb !== FALSE){
                    foreach ($plugindb as $rs) {
                        $sitemap_html.= '<h4> - <a href="'.BASE_URL.'/plugin/'.$row['plugin_urlrewrite'].'/view/'.$rs[$plugin_db[0].'_id'].'/'.$rs['url_rewrite'].'" title="'.str_replace('-', ' ', $rs['url_rewrite']).'">'.str_replace('-', ' ', $rs['url_rewrite']).'</a></h4>';  
                    }
                }
            }
        }
        $sitemap_html.= "</td>
        </tr>
        </table>
        </div>
        </div>
        </body>
        </html>";
        if($sitemap_html){
            $file_path = FCPATH."sitemap.html";
            $fopen = fopen($file_path, 'wb') or die("can't open file");
            fwrite($fopen, $sitemap_html);
            fclose($fopen);
	}
    }
    
}