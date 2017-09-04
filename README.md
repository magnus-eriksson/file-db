# File based database written in PHP

[![Build Status](https://api.travis-ci.org/magnus-eriksson/file-db.svg?branch=master)](https://travis-ci.org/magnus-eriksson/file-db)

* [Install](#install)
* [Instantiate](#instantiate)
* [Storage drivers](#storage-drivers)
    * [File system](#file-system)
    * [Memory](#memory)
* [Query builder](#query-builder)
    * [Tables](#tables)
    * [Insert](#insert)
        * [Batch insert](#batch-insert)
    * [Get data](#get-data)
        * [Get all items](#get-all-items)
        * [Get first item](#get-first-item)
        * [Where](#where)
        * [Order by](#order-by)
        * [Limit](#limit)
        * [Offset](#offset)
    * [Update](#update)


## Install

Using composer:

    composer require maer/file-db

## Instantiate

To create an instance, you need to pass which [storage driver](#storage-drivers) you want to use:

```php
$driver = new SomeStorageDriver();

$db = new Maer\FileDB\FileDB($driver);
```

## Storage drivers


### File system

File system stores the data, as suggested, on the file system. This is the driver you need to use if you want your data to be persistent between requests:

```php
$driver = new Maer\FileDB\Storage\FileSystem(
    '/absolute-path/to/your/storage/folder'
);
```

The storage folder must be writable.

>**Important:** Since the data will be saved in json-format, this folder should also be placed _outside_ of the document root, or people will be able to access the data directly.

### Memory

The memory driver only stores the data in memory for the current request and will _not_ be persistent between requests. This driver is primarily meant to be used as a mock driver for tests.

```php
$driver = new Maer\FileDB\Storage\Memory();
```

## Query builder

### Tables

You can have as many tables (or rather collections) as you want. Each table will be stored in it's own file.

When you get a table from the File DB, you're actually getting a new instance of the [Query builder](#query-builder) (`Maer\FileDB\QueryBuilder`) so you can start building your query:

```php
$query = $db->table('people');

// or shorthand

$query = $db->people;
```

If that table doesn't exist, it will automatically be created when you store data in it for the first time.

### Insert

Inserting data is simple. Simply pass the data as an associative array:

```php
$id = $db->people->insert([
    'first_name'   => 'Chuck',
    'last_name'    => 'Norris',
    'masters'      => 'everything',
]);
```

If the insert was successful, this will return the new ID. If the insert failed, `null` will be returned.

> **Note on ID's:** When you add an item and no ID is passed (passed as `id`), an unique and random 16 char hex string will be generated.

> If you _do_ pass an ID that already exists in that table, the query will fail and return `null`.

#### Batch insert

If you want to insert multiple items at once, you can use `batchInsert(array $data)`

```php
$ids = $db->people->batchInsert([
    [
        'first_name' => 'Chuck',
        'last_name'  => 'Norris',
    ],
    [
        'first_name' => 'Jackie',
        'last_name'  => 'Chan',
    ]
]);
```

This method returns all the generated ID's.

### Get data

Most of the below items will return a multi dimensional array with the matching items.

#### Get all items

```php
$rows = $db->people->get();
```

#### Get first item

Return the first matched item.

```php
$row = $db->people->first();
```

##### Where


Usually, you only want to return some specific items which match some type of criteria. You can do this by adding some "where" conditions to your query:

```php
$rows = $db->people->where('masters', 'everything')->get();
```

The above will match all items that has `everything` as the value for the column `masters`. This equals: `where('masters', '=', 'everything')`.

##### Operators
There are many more operators you can use to narrow down the result.

The below operators are used like this: `where($column, $operator, $value)`


    =           Equal to (Loose type comparison)
    !=          Not equal to (Loose type comparison)
    ===         Equal to (Strict type comparison)
    !==         Not equal to (Strict type comparison)
    <           Lower than
    >           Higher than
    <=          Lower or equal to
    >=          Higher or equal to
    *           Contains
    =*          Starts with
    *=          Ends with
    in          Exists in list
    !in         Not exists in list
    regex       Match using regular expressions.
    func        Match using a custom callback (closure as the third argument)
    array_has   Exists in array  (if the value is an array)
    !array_has  Not exists in array (if the value is an array)
    has_col     The column exist
    !has_col    The column does not exist

You can add as many where conditions as you like to the same query. To make it easier, you can chain them:

```php
$result = $db->people->where('col1', '=', 'some value')
    ->where('col2', '!=', 'some other value')
    ...
    ->get();
```

#### Order by

To sort the result in a specific way, you can use `orderBy($column, $order = 'asc')`.

```php
// Ascending order:
$results = $db->people->orderBy('first_name');

// Descending order:
$results = $db->people->orderBy('first_name', 'desc');
```

#### Limit

You can limit the amount of items returned.

```php
// Only get the 2 first matches
$results = $db->people->limit(2)->get();
```

#### Offset

If you need to add an offset (for using with pagination, for example), you can use `offset($offset)`:

```php
// Get all results from the second match and forward.
$results = $db->people->offset(2)->get();
```

### Update

To update an item, use `update(array $data)`:

```php
$success = $db->people
    ->where('first_name', 'Chuck')
    ->where('last_name', 'Norris')
    ->update([
        'middle_name' => 'The king',
    ]);
```

This method returns a boolean. `true` on success and `false` on error.
