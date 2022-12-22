<?php

namespace Botble\Sms\Events;
use Illuminate\Support\Facades\Event;
use Illuminate\Queue\SerializesModels;

class SendSmsEvent extends Event
{
    use SerializesModels;

    /**
     * @var string
     */
    public $content;

    /**
     * @var string
     */
    public $title;


    /**
     * @var array
     */
    public $args;

    /**
     * @var boolean
     */
    public $debug = false;

    /**
     * SendMailEvent constructor.
     * @param string $content
     * @param string $title
     * @param string $to
     * @param array $args
     * @param bool $debug
     */
    public function __construct($content, $args, $debug = false)
    {
        
        $this->content = $content;
        $this->args = $args;
        $this->debug = $debug;
    }
}
