<?php

namespace Coco\SourceWatcher\Core\Extractors;

use Coco\SourceWatcher\Core\Data\Row;
use Coco\SourceWatcher\Core\Exception\SourceWatcherException;
use Coco\SourceWatcher\Core\IO\Inputs\FileInput;
use Coco\SourceWatcher\Core\Step\Extractor;

/**
 * Extracts text from PDF files — handles text-layer, image-only (scanned), and mixed PDFs.
 *
 * For each page:
 * - Tries pdftotext first. If the page has meaningful embedded text, uses it directly.
 * - Otherwise falls back to rendering the page as a PNG via pdftoppm and running Tesseract OCR.
 *
 * Requires: poppler-utils (pdfinfo, pdftotext, pdftoppm) and tesseract-ocr.
 */
class PdfExtractor extends Extractor
{
    private const TEXT_LAYER_THRESHOLD = 20;

    protected string $column = 'text';
    protected string $language = 'eng';
    protected string $pageColumn = 'page';

    protected array $availableOptions = [ 'column', 'language', 'pageColumn' ];

    public function __construct ()
    {
        $this->column = 'text';
        $this->language = 'eng';
        $this->pageColumn = 'page';
    }

    public function getColumn () : string { return $this->column; }
    public function setColumn ( string $column ) : void { $this->column = $column; }
    public function getLanguage () : string { return $this->language; }
    public function setLanguage ( string $language ) : void { $this->language = $language; }
    public function getPageColumn () : string { return $this->pageColumn; }
    public function setPageColumn ( string $pageColumn ) : void { $this->pageColumn = $pageColumn; }

    /**
     * @return Row[]
     * @throws SourceWatcherException
     */
    public function extract () : array
    {
        if ( $this->input === null ) {
            throw new SourceWatcherException( 'An input must be provided.' );
        }

        if ( !( $this->input instanceof FileInput ) ) {
            throw new SourceWatcherException(
                sprintf( 'The input must be an instance of %s', FileInput::class )
            );
        }

        $filePath = $this->input->getInput();
        $pageCount = $this->getPageCount( $filePath );

        if ( $pageCount === 0 ) {
            throw new SourceWatcherException(
                sprintf(
                    'Could not read PDF or determine page count: %s. Ensure poppler-utils is installed.',
                    $filePath
                )
            );
        }

        $tmpDir = sys_get_temp_dir() . '/sw_pdf_' . uniqid( '', true );
        mkdir( $tmpDir, 0755, true );

        $this->result = [];

        try {
            for ( $page = 1; $page <= $pageCount; $page++ ) {
                foreach ( $this->extractPage( $filePath, $page, $tmpDir ) as $row ) {
                    $this->result[] = $row;
                }
            }
        } finally {
            $this->cleanupDir( $tmpDir );
        }

        return $this->result;
    }

    private function getPageCount ( string $filePath ) : int
    {
        exec( 'pdfinfo ' . escapeshellarg( $filePath ) . ' 2>/dev/null', $output );
        foreach ( $output as $line ) {
            if ( preg_match( '/^Pages:\s+(\d+)/i', $line, $matches ) ) {
                return (int) $matches[1];
            }
        }
        return 0;
    }

    /** @return Row[] */
    private function extractPage ( string $filePath, int $page, string $tmpDir ) : array
    {
        $textLines = $this->tryTextLayer( $filePath, $page );
        if ( $textLines !== null ) {
            return $this->linesToRows( $textLines, $page );
        }
        return $this->linesToRows( $this->extractViaOcr( $filePath, $page, $tmpDir ), $page );
    }

    /**
     * Returns lines if the page has a meaningful text layer, null if OCR fallback is needed.
     *
     * @return string[]|null
     */
    private function tryTextLayer ( string $filePath, int $page ) : ?array
    {
        exec(
            'pdftotext -f ' . $page . ' -l ' . $page . ' '
            . escapeshellarg( $filePath ) . ' - 2>/dev/null',
            $output
        );

        $nonWhitespace = preg_replace( '/\s+/', '', implode( '', $output ) );
        if ( strlen( $nonWhitespace ) < self::TEXT_LAYER_THRESHOLD ) {
            return null;
        }

        return $output;
    }

    /** @return string[] */
    private function extractViaOcr ( string $filePath, int $page, string $tmpDir ) : array
    {
        $prefix = $tmpDir . '/page';
        exec(
            'pdftoppm -f ' . $page . ' -l ' . $page . ' -r 300 -png '
            . escapeshellarg( $filePath ) . ' ' . escapeshellarg( $prefix ) . ' 2>/dev/null'
        );

        $language = $this->language !== '' ? $this->language : 'eng';
        $lines = [];

        foreach ( glob( $tmpDir . '/page-*.png' ) ?: [] as $image ) {
            exec(
                'tesseract ' . escapeshellarg( $image ) . ' stdout -l '
                . escapeshellarg( $language ) . ' 2>/dev/null',
                $ocrOutput,
                $returnCode
            );
            if ( $returnCode === 0 ) {
                $lines = array_merge( $lines, $ocrOutput );
            }
            @unlink( $image );
        }

        return $lines;
    }

    /** @return Row[] */
    private function linesToRows ( array $lines, int $page ) : array
    {
        $rows = [];
        foreach ( $lines as $line ) {
            $line = trim( $line );
            if ( $line === '' ) {
                continue;
            }
            $data = [ $this->column => $line ];
            if ( $this->pageColumn !== '' ) {
                $data[$this->pageColumn] = $page;
            }
            $rows[] = new Row( $data );
        }
        return $rows;
    }

    private function cleanupDir ( string $dir ) : void
    {
        if ( !is_dir( $dir ) ) {
            return;
        }
        foreach ( glob( $dir . '/*' ) ?: [] as $file ) {
            @unlink( $file );
        }
        @rmdir( $dir );
    }
}
