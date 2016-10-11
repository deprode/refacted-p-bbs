<?php
use Codeception\Util\Stub;

class SecurityTest extends \Codeception\TestCase\Test
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    private $input;

    protected function _before()
    {
        $this->input = Stub::make('Input', ['server' => function ($n) {
            return 'POST';
        }]);
    }

    protected function _after()
    {
    }

    // tests
    public function testHosts()
    {
        $this->assertEquals(Security::existHost('192.168.11.1'), false);
        $this->assertEquals(Security::existHost('192.168.11.1',['192.168.11.1']), true);
    }

    public function testRequestMethod()
    {
        // リクエストメソッドが一致しているか
        $this->assertEquals(Security::equalRequestMethod($this->input, 'GET'), false);
        $this->assertEquals(Security::equalRequestMethod($this->input, 'POST'), true);
    }

    public function testAuth()
    {
        // 管理用パスワードが存在し、パスワードが一致しているか
        $this->assertEquals(Security::adminAuth('password', 'password'), true);
        $this->assertEquals(Security::adminAuth(null, 'password'), true);
        $this->assertEquals(Security::adminAuth('password', null), false);
        $this->assertEquals(Security::adminAuth('0', 0), false);
        $this->assertEquals(Security::adminAuth('password', 'a1234567'), false);
        $this->assertEquals(Security::adminAuth('password', 'pass' . PHP_EOL . 'word'), false);
    }
}