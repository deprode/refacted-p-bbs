<?php


class PostTest extends \Codeception\TestCase\Test
{
    use \Codeception\Specify;

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

        $this->specify("引数なしで生成するテスト", function() {
            $post = $this->post->post;
            verify(count($post))->equals(10);
            verify($post['no'])->equals(0);
            verify($post['date'])->equals('');
            verify($post['name'])->equals('');
            verify($post['email'])->equals(null);
            verify($post['subject'])->equals(null);
            verify($post['body'])->equals('');
            verify($post['url'])->equals(null);
            verify($post['host'])->equals(null);
            verify($post['delpass'])->equals(null);
            verify($post['unixtime'])->equals(null);
        });
    }

    public function testBuild()
    {
        $data = '';

        $this->post = Post::buildPost($data);
        $this->specify("空文字列", function() {
            verify($this->post)->equals(null);
        });

        $this->post = Post::buildPost(null);
        $this->specify("null", function() {
            verify($this->post)->equals(null);
        });

        $this->post = Post::buildPost("不正な文字列");
        $this->specify("不正な文字列", function() {
            verify($this->post)->equals(null);
        });

        $data = '1<>2015/12/11(Fri) 18:47<>名前<>example@example.com<>(無題)<>body<>http://example.com<>localhost<>$2y$10$i/QnUfHg90qOO67OtwyPoOQkvgNL.DjmHOUxM64/Np5Y1YUtaT496<>1449827263' . "\n";
        $this->post = Post::buildPost($data);
        $this->specify("正しい文字列", function() {
            $post = $this->post->post;
            verify(count($post))->equals(10);
            verify($post['no'])->equals(1);
            verify($post['date'])->equals("2015/12/11(Fri) 18:47");
            verify($post['name'])->equals("名前");
            verify($post['email'])->equals("example@example.com");
            verify($post['subject'])->equals("(無題)");
            verify($post['body'])->equals('body');
            verify($post['url'])->equals("http://example.com");
            verify($post['host'])->equals("localhost");
            verify($post['delpass'])->equals('$2y$10$i/QnUfHg90qOO67OtwyPoOQkvgNL.DjmHOUxM64/Np5Y1YUtaT496');
            verify($post['unixtime'])->equals(1449827263);
        });
    }

    public function testPostStr()
    {
        $this->post = new Post();
        $this->specify("初期値のPost", function() {
            $string = $this->post->getPostStr();
            verify($string)->equals("0<><><><><><><><><>0\n");
        });

        $this->specify("正しい文字列", function() {
            $data = '1<>2015/12/11(Fri) 18:47<>名前<>example@example.com<>(無題)<>body<>http://example.com<>localhost<>$2y$10$i/QnUfHg90qOO67OtwyPoOQkvgNL.DjmHOUxM64/Np5Y1YUtaT496<>1449827263' . "\n";
            $this->post = Post::buildPost($data);
            $string = $this->post->getPostStr();
            verify($string)->equals($data);
        });
    }
}