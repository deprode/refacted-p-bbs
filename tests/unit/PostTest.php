<?php

class PostTest extends \Codeception\TestCase\Test
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    private $post;

    protected function _before()
    {
    }

    protected function _after()
    {
    }

    // tests
    public function testCreate()
    {
        $this->post = new Post();
        $post = $this->post->post;

        // 引数なしで生成するテスト
        $this->assertEquals(count($post), 10);
        $this->assertEquals($post['no'], 0);
        $this->assertEquals($post['date'], '');
        $this->assertEquals($post['name'], '');
        $this->assertEquals($post['email'], null);
        $this->assertEquals($post['subject'], null);
        $this->assertEquals($post['body'], "");
        $this->assertEquals($post['url'], null);
        $this->assertEquals($post['host'], null);
        $this->assertEquals($post['delpass'], null);
        $this->assertEquals($post['unixtime'], null);
    }

    public function testBuild()
    {
        $data = '';

        $this->post = Post::buildPost($data);
        // 空文字列
        $this->assertEquals($this->post, null);

        // null
        $this->post = Post::buildPost(null);
        $this->assertEquals($this->post, null);

        // 不正な文字列
        $this->post = Post::buildPost("");
        $this->assertEquals($this->post, null);

        // 正しい文字列
        $data = '1<>2015/12/11(Fri) 18:47<>名前<>example@example.com<>(無題)<>body<>http://example.com<>localhost<>$2y$10$i/QnUfHg90qOO67OtwyPoOQkvgNL.DjmHOUxM64/Np5Y1YUtaT496<>1449827263' . "\n";
        $this->post = Post::buildPost($data);
        $post = $this->post->post;
        $this->assertEquals(count($post), 10);
        $this->assertEquals($post['no'], 1);
        $this->assertEquals($post['date'], "2015/12/11(Fri) 18:47");
        $this->assertEquals($post['name'], "名前");
        $this->assertEquals($post['email'], "example@example.com");
        $this->assertEquals($post['subject'], "(無題)");
        $this->assertEquals($post['body'], "body");
        $this->assertEquals($post['url'], "http://example.com");
        $this->assertEquals($post['host'], "localhost");
        $this->assertEquals($post['delpass'], '$2y$10$i/QnUfHg90qOO67OtwyPoOQkvgNL.DjmHOUxM64/Np5Y1YUtaT496');
        $this->assertEquals($post['unixtime'], 1449827263);

    }

    public function testPostStr()
    {
        $this->post = new Post();
        // 初期値のPost
        $string = $this->post->getPostStr();
        $this->assertEquals($string, "0<><><><><><><><><>0\n");

        // 正しい文字列
        $data = '1<>2015/12/11(Fri) 18:47<>名前<>example@example.com<>(無題)<>body<>http://example.com<>localhost<>$2y$10$i/QnUfHg90qOO67OtwyPoOQkvgNL.DjmHOUxM64/Np5Y1YUtaT496<>1449827263' . "\n";
        $this->post = Post::buildPost($data);
        $string = $this->post->getPostStr();
        $this->assertEquals($string, $data);
    }
}