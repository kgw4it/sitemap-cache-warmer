<?php
/**
 * Class PHP_Warmer
 */
class PHP_Warmer
{
    var $config;
    var $response;
    var $timer;
    var $sleep_time;
    var $context;
    var $from;
    var $to;
    var $urlProblems = [];
    var $sitemapUrl;
    var $domain;

    function __construct($config)
    {
        $this->config = array_merge(
           [
               // 'key' => 'default',
               'reportProblematicUrls' => false
           ], $config
        );
        $this->sleep_time = (int)$this->get_parameter('sleep', 0);
        $this->from = (int)$this->get_parameter('from', 0);
        $this->to = (int)$this->get_parameter('to', false);
        $this->sitemapUrl = $this->get_parameter('url');
        $this->response = new PHP_Warmer_Response();
        $this->context = stream_context_create(
                [
                        'http' => [
                                'method' => 'GET',
                                'header' => "User-Agent: Cache-Warmer/1.0\r\n",
                        ],
                ]
        );
	    
        ini_set('SMTP', $this->config['SMTP_HOST']);
        ini_set('smtp_port', $this->config['SMTP_PORT']); 
        ini_set('sendmail_from', $this->config['SMTP_MAIL_FROM']);

        $parsedUrl = parse_url($this->sitemapUrl);
        $this->domain = $parsedUrl['host'];
    }

    function run()
    {
        // Disable time limit
        set_time_limit(0);
        $counter = 0;
        // Authenticate request
        if($this->authenticated_request())
        {
            // URL properly added in GET parameter
            if($this->sitemapUrl !== '')
            {
                //Start timer
                $timer = new PHP_Warmer_Timer();
                $timer->start();

                // Discover URL links
                $doneUrls = [];
                $urls = $this->process_sitemap($this->sitemapUrl);
                sort($urls);
                $continue = true;
                $rounds = 0;

                // Visit links
                while ($continue) {
                    $ret = $this->process_urls($urls);
                    $counter += count($ret['doneUrls']);

                    foreach ($ret['doneUrls'] as $url) {
                        $doneUrls[$url] = true;
                        $this->response->set_visited_url($url);
                    }

                    if (!empty($this->to) && $counter > $this->to) {
                        $continue = false;
                    } else {
                        $urls = array_filter($ret['foundUrls'], function($foundUrl) use ($doneUrls) {
                            return !isset($doneUrls[$foundUrl]);
                        });
                    }

                    if (empty($urls)) {
                        $continue = false;
                    }

                    if (++$rounds > 3) {
                        $continue = false;
		    }
                }

                //Stop timer
                $timer->stop();

                // Send timer data to response
                $this->response->set_duration($timer->duration());

                // Done!
                if(sizeof($urls) > 0)
                    $this->response->set_message("Processed sitemap: {$this->sitemapUrl}");
                else
                    $this->response->set_message("Processed sitemap: {$this->sitemapUrl} - but no URL:s were found", 'ERROR');
            }
            else
            {
                $this->response->set_message('Empty url parameter', 'ERROR');
            }
        }
        else
        {
            $this->response->set_message('Incorrect key', 'ERROR');
        }

        if ($this->config['reportProblematicUrls'] && count($this->urlProblems) > 0) {
            @mail($this->config['reportProblematicUrlsTo'], 'Warming cache ends with errors', "Those URLs cannot be warmed:\n" . implode("\n", $this->urlProblems));
        }

        $this->response->display();
    }
	
    function process_urls($urls) {
        $regexUrl = '/(http|https)\:\/\/' . str_replace('.', '\.', $this->domain) . '(\/[^<>"\'# ]*)?/';
        $done = [];
        $found = [];
        foreach($urls as $url) {
            $url_content = @file_get_contents($url, false, $this->context);

            // Prepare info about URLs with error
            if ($url_content === false && $this->config['reportProblematicUrls']) {
                $this->urlProblems[] = $url;
            } else {
                // check for more urls in the response
                $foundUrls = [];
                if(preg_match_all($regexUrl, $url_content, $foundUrls)) {
                    foreach($foundUrls[0] as $foundUrl) {
                        $found[trim($foundUrl)] = true;
                    }
                }
            }

            $done[$url] = true;

            if(($this->sleep_time > 0)) {
                sleep($this->sleep_time);
            }
        }

        return [
            'doneUrls' => array_keys($done),
            'foundUrls' => array_keys($found),
        ];
    }

    function process_sitemap($url)
    {
        // URL:s array
        $urls = array();

        // Grab sitemap and load into SimpleXML
        $sitemap_xml = @file_get_contents($url,false,$this->context);

        if(($sitemap = @simplexml_load_string($sitemap_xml)) !== false)
        {
            // Process all sub-sitemaps
            if(count($sitemap->sitemap) > 0)
            {
                foreach($sitemap->sitemap as $sub_sitemap)
                {
                    $sub_sitemap_url = trim((string)$sub_sitemap->loc);
                    $urls = array_merge($urls, $this->process_sitemap($sub_sitemap_url));
                    $this->response->log("Processed sub-sitemap: {$sub_sitemap_url}");
                }
            }

            // Process all URL:s
            if(count($sitemap->url) > 0)
            {
                foreach($sitemap->url as $single_url)
                {
                    $urls[] = trim((string)$single_url->loc);
                }
            }

            return $urls;
        }
        else
        {
            $this->response->set_message('Error when loading sitemap.', 'ERROR');
            return array();
        }
    }

    /**
     * @return bool
     */
    function authenticated_request()
    {
        return ($this->get_parameter('key') === $this->config['key']) ? true : false;
    }

    /**
     * @param $key
     * @param string $default_value
     * @return mixed
     */
    function get_parameter($key,  $default_value = '')
    {
        return isset($_GET[$key]) ? $_GET[$key] : $default_value;
    }
}
