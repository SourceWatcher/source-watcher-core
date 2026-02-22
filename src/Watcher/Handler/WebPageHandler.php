<?php

namespace Coco\SourceWatcher\Watcher\Handler;

use Coco\SourceWatcher\Core\SourceWatcherException;
use DOMDocument;

/**
 * Class WebPageHandler
 * @package Coco\SourceWatcher\Watcher\Handler
 */
class WebPageHandler implements Handler
{
    protected string $url;

    protected string $html;

    protected DOMDocument $dom;

    public function __construct ( string $url )
    {
        $this->url = $url;
        $this->html = "";
        $this->dom = new DOMDocument();
    }

    public function getUrl () : string
    {
        return $this->url;
    }

    public function setUrl ( string $url ) : void
    {
        $this->url = $url;
    }

    /**
     * @throws SourceWatcherException
     */
    public function read () : void
    {
        if ( empty( $this->url ) ) {
            throw new SourceWatcherException( "A URL must be set before reading" );
        }

        $ch = curl_init();

        $timeout = 5;

        curl_setopt( $ch, CURLOPT_URL, $this->url );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, $timeout );

        $this->html = curl_exec( $ch );

        # Create a DOM parser object
        $this->dom = new DOMDocument();

        if ( isset( $this->html ) && $this->html !== "" ) {
            # The @ before the method call suppresses any warnings that loadHTML might throw because of invalid HTML in the page.
            @$this->dom->loadHTML( $this->html );
        }
    }

    public function getHtml () : string
    {
        return $this->html;
    }

    public function getDom () : DOMDocument
    {
        return $this->dom;
    }
}
