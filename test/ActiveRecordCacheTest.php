<?php
use ActiveRecord\Cache;

class ActiveRecordCacheTest extends DatabaseTest
{
	public function set_up($connection_name=null)
	{
		if (!extension_loaded('memcached'))
		{
			$this->markTestSkipped('The memcache extension is not available');
			return;
		}

		parent::set_up($connection_name);

		$memcachedHost =  getenv('PHPAR_MEMCACHED') ?: 'localhost';
		ActiveRecord\Config::instance()->set_cache("memcache://$memcachedHost");
	}

	public function tear_down()
	{
		Cache::flush();
		Cache::initialize(null);
	}

	public function test_default_expire()
	{
		$this->assert_equals(30,Cache::$options['expire']);
	}

	public function test_explicit_default_expire()
	{
		$memcachedHost =  getenv('PHPAR_MEMCACHED') ?: 'localhost';
		ActiveRecord\Config::instance()->set_cache("memcache://$memcachedHost",array('expire' => 1));
		$this->assert_equals(1,Cache::$options['expire']);
	}

	public function test_caches_column_meta_data()
	{
		Author::first();

		$table_name = Author::table()->get_fully_qualified_table_name(!($this->conn instanceof ActiveRecord\PgsqlAdapter));
		$value = Cache::$adapter->read("get_meta_data-$table_name");
		$this->assert_true(is_array($value));
	}
}

