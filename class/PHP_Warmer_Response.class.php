<?php
/**
 * Defines a response from the PHP Warmer class
 *
 * Class PHP_Warmer_Response
 */
class PHP_Warmer_Response
{
    var $message;
    var $status;
    var $log;
    var $visited_urls = [];
    var $problem_urls = [];
    var $duration;
    var $count;

    function __construct($message = '', $status = 'OK')
    {
        $this->set_message($message, $status);
        $this->log = array();
        $this->visited_urls = [];
        $this->problem_urls = [];
    }

    function log($entry)
    {
        $this->log[] = $entry;
    }

    function display()
    {
        if (php_sapi_name() !== 'cli') {
            header('Content-Type: application/json');
        }

        echo json_encode(
            [
                'status' => $this->status,
                'message' => $this->message,
                'count' => $this->count,
                'duration' => $this->duration,
                'log' => $this->log,
                'visited_urls' => $this->visited_urls,
                'problem_urls' => $this->problem_urls,
            ],
            php_sapi_name() === 'cli' ? JSON_PRETTY_PRINT : 0
        );
    }

    function set_message($message, $status = 'OK')
    {
        $this->message = $message;
        $this->status = $status;
    }

    function set_count($count)
    {
        $this->count = $count;
    }

    function set_visited_url($url)
    {
        $this->visited_urls[] = $url;
        $this->count++;
    }

    function set_duration($duration)
    {
        $this->duration = $duration;
    }

    function set_problem_urls($problem_urls)
    {
        $this->problem_urls = $problem_urls;
    }
}
