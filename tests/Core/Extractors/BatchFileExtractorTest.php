<?php

namespace Coco\SourceWatcher\Tests\Core\Extractors;

use Coco\SourceWatcher\Core\Data\Row;
use Coco\SourceWatcher\Core\Extractors\BatchFileExtractor;
use Coco\SourceWatcher\Core\Exception\SourceWatcherException;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class BatchFileExtractorTest extends TestCase
{
    public function testExtractRequiresInnerExtractor () : void
    {
        $batch = new BatchFileExtractor();
        $this->expectException( SourceWatcherException::class );
        $this->expectExceptionMessage( 'innerExtractor' );
        $batch->extract();
    }

    public function testExtractRejectsRecursiveInner () : void
    {
        $batch = new BatchFileExtractor();
        $batch->options( [
            'inner_extractor' => 'Batch_File',
            'directory' => sys_get_temp_dir(),
            'glob' => '*.none',
        ] );
        $this->expectException( SourceWatcherException::class );
        $this->expectExceptionMessage( 'cannot be used as innerExtractor' );
        $batch->extract();
    }

    public function testExtractRequiresDirectoryOrFilePaths () : void
    {
        $batch = new BatchFileExtractor();
        $batch->options( [ 'inner_extractor' => 'Txt', 'inner_options' => [ 'column' => 'line' ] ] );
        $this->expectException( SourceWatcherException::class );
        $this->expectExceptionMessage( 'filePaths or directory' );
        $batch->extract();
    }

    public function testExtractGlobConcatenatesWithSourceColumn () : void
    {
        $dir = sys_get_temp_dir() . '/sw_batch_' . uniqid( '', true );
        mkdir( $dir, 0755, true );
        $this->assertTrue( is_dir( $dir ) );

        try {
            file_put_contents( $dir . '/b.txt', "two\n" );
            file_put_contents( $dir . '/a.txt', "one\n" );

            $batch = new BatchFileExtractor();
            $batch->options( [
                'inner_extractor' => 'Txt',
                'inner_options' => [ 'column' => 'line' ],
                'directory' => $dir,
                'glob' => '*.txt',
                'file_column' => 'source_file',
            ] );

            $rows = $batch->extract();
            $this->assertCount( 2, $rows );

            $byLine = [];

            foreach ( $rows as $row ) {
                $byLine[$row['line']] = $row['source_file'];
            }

            $this->assertArrayHasKey( 'one', $byLine );
            $this->assertArrayHasKey( 'two', $byLine );
            $this->assertStringEndsWith( 'a.txt', $byLine['one'] );
            $this->assertStringEndsWith( 'b.txt', $byLine['two'] );
        } finally {
            @unlink( $dir . '/a.txt' );
            @unlink( $dir . '/b.txt' );
            @rmdir( $dir );
        }
    }

    public function testFilePathsOverridesDirectory () : void
    {
        $f = tempnam( sys_get_temp_dir(), 'swb_' );
        $this->assertNotFalse( $f );
        file_put_contents( $f, "only\n" );

        try {
            $batch = new BatchFileExtractor();
            $batch->options( [
                'inner_extractor' => 'Txt',
                'inner_options' => [ 'column' => 'line' ],
                'directory' => '/this/does/not/exist',
                'file_paths' => [ $f ],
                'file_column' => '',
            ] );

            $rows = $batch->extract();
            $this->assertCount( 1, $rows );
            $this->assertSame( 'only', $rows[0]['line'] );
            $this->assertFalse( isset( $rows[0]['source_file'] ) );
        } finally {
            @unlink( $f );
        }
    }

    public function testOnErrorStopPropagates () : void
    {
        $dir = sys_get_temp_dir() . '/sw_batch_err_' . uniqid( '', true );
        mkdir( $dir, 0755, true );
        $good = $dir . '/0_good.json';
        $bad = $dir . '/1_bad.json';
        file_put_contents( $good, '[{"line":"ok"}]' );
        file_put_contents( $bad, 'not valid json {{{' );

        try {
            $batch = new BatchFileExtractor();
            $batch->options( [
                'inner_extractor' => 'Json',
                'inner_options' => [],
                'file_paths' => [ $good, $bad ],
                'on_error' => 'stop',
                'file_column' => '',
            ] );

            $this->expectException( SourceWatcherException::class );
            $this->expectExceptionMessage( 'Invalid JSON' );
            $batch->extract();
        } finally {
            @unlink( $good );
            @unlink( $bad );
            @rmdir( $dir );
        }
    }

    public function testOnErrorSkipContinues () : void
    {
        $dir = sys_get_temp_dir() . '/sw_batch_skip_' . uniqid( '', true );
        mkdir( $dir, 0755, true );
        $bad = $dir . '/0_bad.json';
        $good = $dir . '/1_good.json';
        file_put_contents( $bad, 'not valid json {{{' );
        file_put_contents( $good, '[{"line":"ok"}]' );

        try {
            $batch = new BatchFileExtractor();
            $batch->options( [
                'inner_extractor' => 'Json',
                'inner_options' => [],
                'file_paths' => [ $bad, $good ],
                'on_error' => 'skip',
                'file_column' => '',
            ] );

            $rows = $batch->extract();
            $this->assertCount( 1, $rows );
            $this->assertSame( 'ok', $rows[0]['line'] );
        } finally {
            @unlink( $bad );
            @unlink( $good );
            @rmdir( $dir );
        }
    }

    public function testInvalidOnError () : void
    {
        $f = tempnam( sys_get_temp_dir(), 'swboe_' );
        file_put_contents( $f, "x\n" );

        try {
            $batch = new BatchFileExtractor();
            $batch->options( [
                'inner_extractor' => 'Txt',
                'inner_options' => [ 'column' => 'line' ],
                'file_paths' => [ $f ],
                'on_error' => 'retry',
            ] );

            $this->expectException( SourceWatcherException::class );
            $this->expectExceptionMessage( 'onError' );
            $batch->extract();
        } finally {
            @unlink( $f );
        }
    }

    public function testOnErrorStopIsTrimmedAndCaseInsensitive () : void
    {
        $f = tempnam( sys_get_temp_dir(), 'swbtrim_' );
        file_put_contents( $f, "x\n" );

        try {
            $batch = new BatchFileExtractor();
            $batch->options( [
                'inner_extractor' => 'Txt',
                'inner_options' => [ 'column' => 'line' ],
                'file_paths' => [ $f ],
                'on_error' => '  STOP  ',
            ] );

            $rows = $batch->extract();
            $this->assertCount( 1, $rows );
        } finally {
            @unlink( $f );
        }
    }

    public function testRejectsRecursiveInnerCaseInsensitive () : void
    {
        $batch = new BatchFileExtractor();
        $batch->options( [
            'inner_extractor' => 'batch_file',
            'directory' => sys_get_temp_dir(),
            'glob' => '*.none_sw_batch_ci_' . uniqid(),
        ] );
        $this->expectException( SourceWatcherException::class );
        $this->expectExceptionMessage( 'cannot be used as innerExtractor' );
        $batch->extract();
    }

    public function testUnknownInnerExtractor () : void
    {
        $f = tempnam( sys_get_temp_dir(), 'swbuk_' );
        file_put_contents( $f, "x\n" );

        try {
            $batch = new BatchFileExtractor();
            $batch->options( [
                'inner_extractor' => 'DefinitelyMissingStepNameXyz',
                'file_paths' => [ $f ],
            ] );

            $this->expectException( SourceWatcherException::class );
            $this->expectExceptionMessage( 'Unknown inner extractor' );
            $batch->extract();
        } finally {
            @unlink( $f );
        }
    }

    public function testFilePathsSkipsEmptyEntries () : void
    {
        $f = tempnam( sys_get_temp_dir(), 'swbemp_' );
        file_put_contents( $f, "line\n" );

        try {
            $batch = new BatchFileExtractor();
            $batch->options( [
                'inner_extractor' => 'Txt',
                'inner_options' => [ 'column' => 'line' ],
                'file_paths' => [ '', $f ],
                'file_column' => '',
            ] );

            $rows = $batch->extract();
            $this->assertCount( 1, $rows );
        } finally {
            @unlink( $f );
        }
    }

    public function testFilePathsRejectsDirectoryPath () : void
    {
        $dir = sys_get_temp_dir() . '/sw_batch_isdir_' . uniqid( '', true );
        mkdir( $dir, 0755, true );

        try {
            $batch = new BatchFileExtractor();
            $batch->options( [
                'inner_extractor' => 'Txt',
                'inner_options' => [ 'column' => 'line' ],
                'file_paths' => [ $dir ],
            ] );

            $this->expectException( SourceWatcherException::class );
            $this->expectExceptionMessage( 'Not a file' );
            $batch->extract();
        } finally {
            @rmdir( $dir );
        }
    }

    public function testDirectoryMustExist () : void
    {
        $batch = new BatchFileExtractor();
        $batch->options( [
            'inner_extractor' => 'Txt',
            'inner_options' => [ 'column' => 'line' ],
            'directory' => sys_get_temp_dir() . '/sw_batch_no_such_dir_' . uniqid( '', true ),
            'glob' => '*',
        ] );

        $this->expectException( SourceWatcherException::class );
        $this->expectExceptionMessage( 'Not a directory' );
        $batch->extract();
    }

    public function testGlobNoMatchesThrows () : void
    {
        $dir = sys_get_temp_dir() . '/sw_batch_empty_glob_' . uniqid( '', true );
        mkdir( $dir, 0755, true );

        try {
            $batch = new BatchFileExtractor();
            $batch->options( [
                'inner_extractor' => 'Txt',
                'inner_options' => [ 'column' => 'line' ],
                'directory' => $dir,
                'glob' => '*.no_such_extension_' . uniqid( '', true ),
            ] );

            $this->expectException( SourceWatcherException::class );
            $this->expectExceptionMessage( 'No files matched' );
            $batch->extract();
        } finally {
            @rmdir( $dir );
        }
    }

    public function testFilePathsOnlyEmptyStringsNoMatches () : void
    {
        $batch = new BatchFileExtractor();
        $batch->options( [
            'inner_extractor' => 'Txt',
            'inner_options' => [ 'column' => 'line' ],
            'file_paths' => [ '', '' ],
        ] );

        $this->expectException( SourceWatcherException::class );
        $this->expectExceptionMessage( 'No files matched' );
        $batch->extract();
    }

    public function testGlobCollectsOnlyFilesSkipsSubdirectories () : void
    {
        $dir = sys_get_temp_dir() . '/sw_batch_mix_' . uniqid( '', true );
        mkdir( $dir, 0755, true );
        mkdir( $dir . '/nested', 0755, true );
        file_put_contents( $dir . '/only.txt', "one\n" );

        try {
            $batch = new BatchFileExtractor();
            $batch->options( [
                'inner_extractor' => 'Txt',
                'inner_options' => [ 'column' => 'line' ],
                'directory' => $dir,
                'glob' => '*',
                'file_column' => '',
            ] );

            $rows = $batch->extract();
            $this->assertCount( 1, $rows );
            $this->assertSame( 'one', $rows[0]['line'] );
        } finally {
            @unlink( $dir . '/only.txt' );
            @rmdir( $dir . '/nested' );
            @rmdir( $dir );
        }
    }

    public function testTagRowsAddsColumnForRowAndPassesThroughNonRow () : void
    {
        $batch = new BatchFileExtractor();
        $batch->options( [ 'file_column' => 'src' ] );

        $tagRows = new ReflectionMethod( BatchFileExtractor::class, 'tagRows' );

        $mixed = [
            new Row( [ 'a' => 1 ] ),
            'scalar-row',
        ];

        $out = $tagRows->invoke( $batch, $mixed, '/tmp/example.txt' );

        $this->assertCount( 2, $out );
        $this->assertInstanceOf( Row::class, $out[0] );
        $this->assertSame( 1, $out[0]['a'] );
        $this->assertSame( '/tmp/example.txt', $out[0]['src'] );
        $this->assertSame( 'scalar-row', $out[1] );
    }

    public function testExtractReturnsEmptyWhenAllSkippedAndOnErrorSkip () : void
    {
        $dir = sys_get_temp_dir() . '/sw_batch_all_skip_' . uniqid( '', true );
        mkdir( $dir, 0755, true );
        $bad = $dir . '/0_bad.json';
        $bad2 = $dir . '/1_bad.json';
        file_put_contents( $bad, 'not json' );
        file_put_contents( $bad2, 'not json' );

        try {
            $batch = new BatchFileExtractor();
            $batch->options( [
                'inner_extractor' => 'Json',
                'inner_options' => [],
                'file_paths' => [ $bad, $bad2 ],
                'on_error' => 'skip',
                'file_column' => '',
            ] );

            $rows = $batch->extract();
            $this->assertSame( [], $rows );
        } finally {
            @unlink( $bad );
            @unlink( $bad2 );
            @rmdir( $dir );
        }
    }
}
