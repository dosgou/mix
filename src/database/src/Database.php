<?php

namespace Mix\Database;

use Mix\Bean\BeanInjector;
use Mix\Database\Pool\ConnectionPool;
use Mix\Database\Pool\Dialer;
use Mix\Database\Query\Expression;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * Class Database
 * @package Mix\Database
 */
class Database
{

    /**
     * 数据源格式
     * @var string
     */
    public $dsn = '';

    /**
     * 数据库用户名
     * @var string
     */
    public $username = 'root';

    /*
     * 数据库密码
     */
    public $password = '';

    /**
     * 驱动连接选项
     * @var array
     */
    public $options = [];

    /**
     * 最多可空闲连接数
     * @var int
     */
    public $maxIdle = 5;

    /**
     * 最大连接数
     * @var int
     */
    public $maxActive = 5;

    /**
     * 事件调度器
     * @var EventDispatcherInterface
     */
    public $dispatcher;

    /**
     * @var ConnectionPool
     */
    protected $pool;

    /**
     * AbstractConnection constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        BeanInjector::inject($this, $config);
    }

    /**
     * Init
     */
    public function init()
    {
        $pool       = new ConnectionPool([
            'maxIdle'    => $this->maxIdle,
            'maxActive'  => $this->maxActive,
            'dialer'     => new Dialer([
                'dsn'      => $this->dsn,
                'username' => $this->username,
                'password' => $this->password,
                'options'  => $this->options,
            ]),
            'dispatcher' => $this->dispatcher,
        ]);
        $this->pool = $pool;
    }

    /**
     * Borrow connection
     * @return Connection
     */
    public function borrow(): Connection
    {
        $driver           = $this->pool->borrow();
        $conn             = new Connection($driver);
        $conn->dispatcher = $this->dispatcher;
        return $conn;
    }

    /**
     * 准备执行语句
     * @param string|array $sql
     * @return Connection
     */
    public function prepare($sql): Connection
    {
        return $this->borrow()->prepare($sql);
    }

    /**
     * 插入
     * @param string $table
     * @param array $data
     * @return Connection
     */
    public function insert(string $table, array $data): Connection
    {
        return $this->borrow()->insert($table, $data);
    }

    /**
     * 批量插入
     * @param string $table
     * @param array $data
     * @return Connection
     */
    public function batchInsert(string $table, array $data): Connection
    {
        return $this->borrow()->batchInsert($table, $data);
    }

    /**
     * 更新
     * @param string $table
     * @param array $data
     * @param array $where
     * @return Connection
     */
    public function update(string $table, array $data, array $where): Connection
    {
        return $this->borrow()->update($table, $data, $where);
    }

    /**
     * 删除
     * @param string $table
     * @param array $where
     * @return Connection
     */
    public function delete(string $table, array $where): Connection
    {
        return $this->borrow()->delete($table, $where);
    }

    /**
     * 自动事务
     * @param \Closure $closure
     * @throws \Throwable
     */
    public function transaction(\Closure $closure)
    {
        return $this->borrow()->transaction($closure);
    }

    /**
     * 开始事务
     * @return Connection
     */
    public function beginTransaction(): Connection
    {
        return $this->borrow()->beginTransaction();
    }

    /**
     * 启动查询生成器
     * @param string $table
     * @return QueryBuilder
     */
    public function table(string $table): QueryBuilder
    {
        return $this->borrow()->table($table);
    }

    /**
     * 返回一个RawQuery对象，对象的值将不经过参数绑定，直接解释为SQL的一部分，适合传递数据库原生函数
     * @param string $value
     * @return Expression
     */
    public static function raw(string $value): Expression
    {
        return new Expression($value);
    }

}
