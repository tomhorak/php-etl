<?php

namespace Tests\Loaders;

use DateTime;
use Tests\TestCase;
use Marquine\Metis\Metis;
use Marquine\Metis\Loaders\Table;

class TableTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->setUpDatabase();
    }

    /** @test */
    function insert_data_into_table()
    {
        $items = [
            ['id' => '1', 'name' => 'John Doe', 'email' => 'johndoe@email.com'],
            ['id' => '2', 'name' => 'Jane Doe', 'email' => 'janedoe@email.com'],
        ];

        $loader = new Table;

        $loader->load('users', $items);

        $results = Metis::connection()->table('users')->select();

        $this->assertEquals($items, $results);
    }

    /** @test */
    function update_table_data()
    {
        Metis::connection()->exec("insert into users values (1, 'John Doe', 'johndoe@email.com')");
        Metis::connection()->exec("insert into users values (2, 'Jane', 'janedoe@email.com')");

        $items = [
            ['id' => '1', 'name' => 'John Doe', 'email' => 'johndoe@email.com'],
            ['id' => '2', 'name' => 'Jane Doe', 'email' => 'janedoe@email.com'],
        ];

        $loader = new Table;

        $loader->load('users', $items);

        $results = Metis::connection()->table('users')->select();

        $this->assertEquals($items, $results);
    }

    /** @test */
    function delete_records_that_are_not_in_the_source()
    {
        Metis::connection()->exec("insert into users values (1, 'John Doe', 'johndoe@email.com')");
        Metis::connection()->exec("insert into users values (2, 'Jane Doe', 'janedoe@email.com')");

        $items = [
            ['id' => '1', 'name' => 'John', 'email' => 'johndoe@email.com'],
        ];

        $options = ['delete' => true];

        $loader = new Table($options);

        $loader->load('users', $items);

        $results = Metis::connection()->table('users')->select();

        $this->assertEquals($items, $results);
    }

    /** @test */
    function insert_data_into_table_with_timestamps()
    {
        $items = [
            ['id' => '1', 'name' => 'John Doe', 'email' => 'johndoe@email.com'],
            ['id' => '2', 'name' => 'Jane Doe', 'email' => 'janedoe@email.com'],
        ];

        $options = ['timestamps' => true];

        $loader = new Table($options);

        $loader->load('users_ts', $items);

        $results = Metis::connection()->table('users_ts')->select();

        foreach ($results as $row) {
            $this->assertTrue((bool) DateTime::createFromFormat('Y-m-d G:i:s', $row['created_at']));
            $this->assertTrue((bool) DateTime::createFromFormat('Y-m-d G:i:s', $row['updated_at']));
            $this->assertEquals($row['created_at'], $row['updated_at']);
        }
    }

    /** @test */
    function update_table_data_with_timestamps()
    {
        Metis::connection()->exec("insert into users_ts (id, name, email) values (1, 'John', 'johndoe@email.com')");
        Metis::connection()->exec("insert into users_ts (id, name, email) values (2, 'Jane', 'janedoe@email.com')");

        $items = [
            ['id' => '1', 'name' => 'John Doe', 'email' => 'johndoe@email.com'],
            ['id' => '2', 'name' => 'Jane Doe', 'email' => 'janedoe@email.com'],
        ];

        $options = ['timestamps' => true];

        $loader = new Table($options);

        $loader->load('users_ts', $items);

        $results = Metis::connection()->table('users_ts')->select();

        foreach ($results as $row) {
            $this->assertTrue((bool) DateTime::createFromFormat('Y-m-d G:i:s', $row['updated_at']));
            $this->assertNull($row['created_at']);
        }
    }

    /** @test */
    function soft_delete_records_that_are_not_in_the_source()
    {
        Metis::connection()->exec("insert into users_ts (id, name, email) values (1, 'John Doe', 'johndoe@email.com')");
        Metis::connection()->exec("insert into users_ts (id, name, email) values (2, 'Jane Doe', 'janedoe@email.com')");

        $items = [];

        $options = ['delete' => 'soft'];

        $loader = new Table($options);

        $loader->load('users_ts', $items);

        $results = Metis::connection()->table('users_ts')->select();

        $this->assertNotEmpty($results);

        foreach ($results as $row) {
            $this->assertTrue((bool) DateTime::createFromFormat('Y-m-d G:i:s', $row['deleted_at']));
        }
    }
}