<?php

namespace Coco\SourceWatcher\Core\Extractors;

use Coco\SourceWatcher\Core\Data\Row;
use Coco\SourceWatcher\Core\Exception\SourceWatcherException;
use Coco\SourceWatcher\Core\IO\Inputs\FileInput;
use Coco\SourceWatcher\Core\Pipeline\StepLoader;
use Coco\SourceWatcher\Core\Step\Extractor;

/**
 * Runs a file-based extractor once per matched file and concatenates rows.
 *
 * StepLoader name: Batch_File (see docs/BATCH-EXTRACTOR-AND-EXECUTOR.md).
 */
class BatchFileExtractor extends Extractor
{
    protected string $innerExtractor = '';

    protected array $innerOptions = [];

    protected string $directory = '';

    protected string $glob = '*';

    protected array $filePaths = [];

    protected string $fileColumn = 'source_file';

    protected string $onError = 'stop';

    protected array $availableOptions = [
        'innerExtractor',
        'innerOptions',
        'directory',
        'glob',
        'filePaths',
        'fileColumn',
        'onError',
    ];

    /**
     * @return Row[]
     * @throws SourceWatcherException
     */
    public function extract () : array
    {
        if ( $this->innerExtractor === '' ) {
            throw new SourceWatcherException( 'innerExtractor is required for Batch_File.' );
        }

        if ( strcasecmp( $this->innerExtractor, 'Batch_File' ) === 0 ) {
            throw new SourceWatcherException( 'Batch_File cannot be used as innerExtractor.' );
        }

        $mode = strtolower( trim( $this->onError ) );

        if ( $mode !== 'stop' && $mode !== 'skip' ) {
            throw new SourceWatcherException( 'onError must be "stop" or "skip".' );
        }

        $paths = $this->resolvePaths();

        $stepLoader = new StepLoader();
        $this->result = [];

        foreach ( $paths as $path ) {
            $inner = $stepLoader->getStep( Extractor::class, $this->innerExtractor );

            if ( $inner === null ) {
                throw new SourceWatcherException(
                    sprintf( 'Unknown inner extractor: %s', $this->innerExtractor )
                );
            }

            try {
                $inner->setInput( new FileInput( $path ) );
                $inner->options( $this->innerOptions );
                $rows = $inner->extract();
            } catch ( \Throwable $throwable ) {
                if ( $mode === 'skip' ) {
                    continue;
                }

                throw $throwable;
            }

            foreach ( $this->tagRows( $rows, $path ) as $row ) {
                $this->result[] = $row;
            }
        }

        return $this->result;
    }

    /**
     * @return string[]
     * @throws SourceWatcherException
     */
    private function resolvePaths () : array
    {
        $paths = [];

        if ( !empty( $this->filePaths ) ) {
            foreach ( $this->filePaths as $p ) {
                $p = (string) $p;

                if ( $p === '' ) {
                    continue;
                }

                $real = realpath( $p );

                if ( $real === false || !is_file( $real ) ) {
                    throw new SourceWatcherException( sprintf( 'Not a file: %s', $p ) );
                }

                $paths[] = $real;
            }
        } else {
            if ( $this->directory === '' ) {
                throw new SourceWatcherException( 'Provide filePaths or directory for Batch_File.' );
            }

            $realDir = realpath( $this->directory );

            if ( $realDir === false || !is_dir( $realDir ) ) {
                throw new SourceWatcherException( sprintf( 'Not a directory: %s', $this->directory ) );
            }

            $pattern = $realDir . DIRECTORY_SEPARATOR . $this->glob;
            $matched = glob( $pattern ) ?: [];

            foreach ( $matched as $f ) {
                if ( is_file( $f ) ) {
                    $paths[] = $f;
                }
            }
        }

        sort( $paths, SORT_STRING );

        if ( $paths === [] ) {
            throw new SourceWatcherException( 'No files matched the batch criteria.' );
        }

        return $paths;
    }

    /**
     * @param Row[] $rows
     * @return Row[]
     */
    private function tagRows ( array $rows, string $path ) : array
    {
        if ( $this->fileColumn === '' ) {
            return $rows;
        }

        $out = [];

        foreach ( $rows as $row ) {
            if ( !( $row instanceof Row ) ) {
                $out[] = $row;

                continue;
            }

            $attrs = $row->getAttributes();
            $attrs[$this->fileColumn] = $path;
            $out[] = new Row( $attrs );
        }

        return $out;
    }
}
