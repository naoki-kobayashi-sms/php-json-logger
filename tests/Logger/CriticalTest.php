<?php
namespace Nekonomokochan\Tests\Logger;

use Nekonomokochan\PhpJsonLogger\LoggerBuilder;
use PHPUnit\Framework\TestCase;

/**
 * Class CriticalTest
 *
 * @package Nekonomokochan\Tests\Logger
 * @see \Nekonomokochan\PhpJsonLogger\Logger::critical
 */
class CriticalTest extends TestCase
{
    /**
     * @var string
     */
    private $outputFileBaseName;

    /**
     * @var string
     */
    private $outputFileName;

    /**
     * Delete the log file used last time to test the contents of the log file
     */
    public function setUp()
    {
        parent::setUp();
        $this->outputFileBaseName = '/tmp/critical-log-test.log';
        $this->outputFileName = '/tmp/critical-log-test-' . date('Y-m-d') . '.log';

        if (file_exists($this->outputFileName)) {
            unlink($this->outputFileName);
        }
    }

    /**
     * @test
     */
    public function outputCriticalLog()
    {
        $exception = new \ErrorException('TestCritical', 500);
        $context = [
            'name'  => 'keitakn',
            'email' => 'dummy@email.com',
        ];

        $loggerBuilder = new LoggerBuilder();
        $loggerBuilder->setFileName($this->outputFileBaseName);
        $logger = $loggerBuilder->build();
        $logger->critical($exception, $context);

        $resultJson = file_get_contents($this->outputFileName);
        $resultArray = json_decode($resultJson, true);

        echo "\n ---- Output Log Begin ---- \n";
        echo json_encode($resultArray, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        echo "\n ---- Output Log End   ---- \n";

        $expectedLog = [
            'log_level'         => 'CRITICAL',
            'message'           => 'ErrorException',
            'trace_id'          => $logger->getTraceId(),
            'file'              => __FILE__,
            'line'              => 53,
            'context'           => $context,
            'remote_ip_address' => '127.0.0.1',
            'user_agent'        => 'unknown',
            'datetime'          => $resultArray['datetime'],
            'timezone'          => date_default_timezone_get(),
            'process_time'      => $resultArray['process_time'],
            'errors'            => [
                'message' => 'TestCritical',
                'code'    => 500,
                'file'    => __FILE__,
                'line'    => 44,
                'trace'   => $resultArray['errors']['trace'],
            ],
        ];

        $this->assertSame('PhpJsonLogger', $logger->getMonologInstance()->getName());
        $this->assertSame($expectedLog, $resultArray);
    }
}