<?php

namespace Coco\SourceWatcher\Core\Transformers;

use Coco\SourceWatcher\Core\Row;
use Coco\SourceWatcher\Core\Transformer;
use Coco\SourceWatcher\Utils\FileUtils;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

/**
 * Class JavaTransformer
 *
 * @package Coco\SourceWatcher\Core\Transformers
 */
class JavaTransformer extends Transformer
{
    protected string $classpath = "";
    protected string $classname = "";
    protected array $arguments = [];
    protected string $resultType = "";

    protected array $availableOptions = [ "classpath", "classname", "arguments", "resultType" ];

    private Logger $logger;

    public function __construct ()
    {
        $this->logger = new Logger( "JavaTransformer" );
    }

    private function getArguments ( Row $row ) : string
    {
        $arguments = "";

        foreach ( $this->arguments as $currentArgument ) {
            if ( $currentArgument->getType() == JavaTransformerArgumentType::ARG_TYPE_COLUMN ) {
                $arguments .= " " . $row[$currentArgument->getColumnValue()];
            }

            if ( $currentArgument->getType() == JavaTransformerArgumentType::ARG_TYPE_STRING ) {
                $arguments .= " " . $currentArgument->getStringValue();
            }

            if ( $currentArgument->getType() == JavaTransformerArgumentType::ARG_TYPE_MIXED ) {
                $arguments .= " " . $currentArgument->getMixedKey() . "=" . $row[$currentArgument->getMixedVal()];
            }
        }

        return trim( $arguments );
    }

    private function getCommand ( Row $row ) : string
    {
        return "java -cp " . $this->classpath . " " . $this->classname . " " . $this->getArguments( $row );
    }

    private bool $logHasBeenSet = false;

    private function setLogHandler () : void
    {
        if ( !$this->logHasBeenSet ) {
            $streamPath = FileUtils::file_build_path( __DIR__, "..", "..", "..", "logs",
                $this->classname . "-" . gmdate( "Y-m-d-H-i-s", time() ) . "-" . getmypid() . ".txt" );
            $this->logger->pushHandler( new StreamHandler( $streamPath ), Logger::DEBUG );
            $this->logHasBeenSet = true;
        }
    }

    /**
     * Run the Java command. Override in tests to avoid exec().
     *
     * @return array{0: array<string>, 1: int}
     */
    protected function runCommand ( string $command ) : array
    {
        $output = [];
        $returnValue = -1;
        exec( $command, $output, $returnValue );
        return [ $output, $returnValue ];
    }

    public function transform ( Row $row ) : void
    {
        $this->setLogHandler();

        $command = $this->getCommand( $row );
        $this->logger->debug( $command );

        [ $output, $returnValue ] = $this->runCommand( $command );

        $this->logger->debug( print_r( $output, true ) );
        $this->logger->debug( (string) $returnValue );

        if ( $returnValue === 0 ) {
            if ( $this->resultType === JavaTransformerResultType::RESULT_TYPE_JSON && isset( $output[0] ) ) {
                $array = json_decode( $output[0], true );

                if ( !empty( $array ) && is_array( $array ) ) {
                    foreach ( $array as $key => $val ) {
                        $row->set( $key, $val );
                    }
                }
            }
        } else {
            $this->logger->error( "JavaTransformer non-zero return: {returnValue} for row", [
                'returnValue' => $returnValue,
                'row' => $row->getAttributes(),
            ] );
        }
    }
}
