<?php

namespace RocketShip\Database;

use RocketShip\Application;
use RocketShip\Base;
use RocketShip\Configuration;
use RocketShip\Event;
use RocketShip\Filter;
use RocketShip\Utils\Inflector;

class Collection
{
    private static $dbCalls;
    private static $connection;
    private $collection;
    private $isGridFS = false;
    private $query    = ['select' => [], 'where' => [], 'order' => [], 'limit' => '', 'offset' => '', 'paginate' => ''];

    /**
     *
     * __construct
     *
     * Create mongo connection and setup basic configuration for it
     *
     * @access  public
     * @final
     *
     */
    public final function __construct($collection=null, $isGrid=false)
    {
        $config = Configuration::get('database', Application::$_environment);

        if (empty(self::$dbCalls)) {
            self::$dbCalls = 0;
        }

        if (empty(self::$connection)) {
            if (!empty($config->user) && !empty($config->password)) {
                self::$connection = new \MongoClient(
                    "mongodb://{$config->user}:{$config->password}@{$config->host}:{$config->port}/{$config->database}"
                );
            } else {
                self::$connection = new \MongoClient("mongodb://{$config->host}:{$config->port}/{$config->database}");
            }
        }

        if (empty($collection)) {
            $inflector = new Inflector;
            $collection = $inflector->underscore($inflector->pluralize(get_class($this)));
        } else {
            $inflector = new Inflector;
            $collection = $inflector->underscore($inflector->pluralize($collection));
        }

        if ($this->isGridFS == true || $isGrid == true) {
            $this->collection = self::$connection->{$config->database}->getGridFS($collection);
        } else {
            $this->collection = self::$connection->{$config->database}->{$collection};
        }

        if (method_exists($this, 'init')) {
            $this->init();
        }
    }

    /**
     *
     * set
     *
     * Set the collection manually
     *
     * @param   string      collection name
     * @access  public
     *
     */
    public final function set($collection)
    {
        $config = Configuration::get('database', Application::$_environment);

        $inflector  = new Inflector;
        $collection = $inflector->underscore($inflector->pluralize($collection));

        if ($this->isGridFS == true) {
            $this->collection = self::$connection->{$config->dataase}->getGridFS($collection);
        } else {
            $this->collection = self::$connection->{$config->database}->{$collection};
        }
    }

    /**
     *
     * Get a collection instance from the given collection name
     *
     * @param   string              collection name
     * @return  \MongoCollection    collection object
     * @access  private
     * @static
     * @final
     *
     */
    private static final function getCollectionInstance($collection)
    {
        $config = Configuration::get('database', Application::$_environment);

        $inflector  = new Inflector;
        $collection = $inflector->underscore($inflector->pluralize($collection));
        return self::$connection->{$config->database}->{$collection};
    }

    /**
     *
     * Set the select clause
     *
     * @param   string  the select clause
     * @return  object  this object
     * @access  public
     * @throws  \RuntimeException
     * @final
     *
     */
    public final function select($select)
    {
        if (is_string($select)) {
            $fields = explode(",", $select);
            foreach ($fields as $num => $value) {
                $fields[trim($value)] = true;
            }

            $this->query['select'] = $fields;
            return $this;
        } else {
            try {
                $type = gettype($select);
                throw new \RuntimeException("select expects a string to be passed. received {$type}");
            } catch (\RuntimeException $e) {
                $app = Application::$instance;
                $app->debugger->addException($e);
            }
        }
    }

    /**
     *
     * Set the where clause
     *
     * @param   array   the where clause
     * @return  object  this object
     * @access  public
     * @final
     *
     */
    public final function where($where)
    {
        foreach ($where as $key => $value) {
            if ($key == '_id') {
                if (is_string($value)) {
                    $value       = (string)$value;
                    $where[$key] = new \MongoId($value);
                }
            } else {
                $where[$key] = $value;
            }
        }

        $this->query['where'] = $where;
        return $this;
    }

    /**
     *
     * Set the order clause
     *
     * @param   string  the order clause
     * @return  object  this object
     * @access  public
     * @final
     *
     */
    public final function order($order)
    {
        if (is_string($order)) {
            list($field, $sort) = explode(" ", $order);
            if (strtoupper($sort) == 'ASC') {
                $this->query['order'] = [$field => 1];
            } elseif (strtoupper($sort) == 'DESC') {
                $this->query['order'] = [$field => -1];
            }
        } else {
            $this->query['order'] = $order;
        }

        return $this;
    }

    /**
     *
     * Set the limit clause
     *
     * @param   int     the limit clause
     * @return  object  this object
     * @access  public
     * @final
     *
     */
    public final function limit($limit)
    {
        $this->query['limit'] = $limit;
        return $this;
    }

    /**
     *
     * Set the offset clause
     *
     * @param   int     the offset clause
     * @return  object  this object
     * @access  public
     * @final
     *
     */
    public final function offset($offset)
    {
        $this->query['offset'] = $offset;
        return $this;
    }

    /**
     *
     * Get a paginated result object on this query
     *
     * @param   int     page number
     * @param   int     max per page
     * @return  object  this object
     * @access  public
     * @final
     *
     */
    public final function paginate($page, $max)
    {
        $offset = $page * $max - $max;
        $this->offset($offset)->limit($max);
        $this->query['paginate'] = true;
        return $this;
    }

    /**
     *
     * Find a single document
     *
     * @return  mixed   Model class with data set, null if no document is found
     * @access  public
     * @final
     *
     */
    public final function find()
    {
        $app = Application::$instance;
        $app->debugger->startDBTask('find');
        $result = $this->collection->findOne($this->query['where'], $this->query['select']);

        self::$dbCalls++;

        if (!empty($result)) {
            $class    = get_class($this);
            $instance = new $class($this->collection->getName());

            foreach ($result as $key => $value) {
                if (is_array($value)) {
                    $instance->{$key} = $value;
                } elseif (is_object($value) && !stristr(get_class($value), 'Mongo')) {
                    $instance->{$key} = new \stdClass;

                    foreach ($value as $k => $v) {
                        $instance->{$key}->{$k} = $v;
                    }
                } else {
                    $instance->{$key} = $value;
                }
            }

            $app->debugger->endDBTask('find');

            return $instance;
        } else {
            $app->debugger->endDBTask('find');
            return null;
        }
    }

    /**
     *
     * Find all documents that matches query
     *
     * @return  mixed   Array of documents, null if no documents is found
     * @access  public
     * @final
     *
     */
    public final function findAll()
    {
        $app = Application::$instance;
        $app->debugger->startDBTask('findall');

        $request = $this->collection->find($this->query['where'], $this->query['select']);
        $results = [];

        self::$dbCalls++;

        /* Optional query elements */
        if (!empty($this->query['order'])) { $request = $request->sort($this->query['order']); }
        if (!empty($this->query['offset'])) { $request = $request->skip($this->query['offset']); }
        if (!empty($this->query['limit'])) { $request = $request->limit($this->query['limit']); }

        $count = $request->count();

        if ($count == 0) {
            if ($this->query['paginate']) {
                $pagination                = new \stdClass;
                $pagination->total_pages   = 1;
                $pagination->current_page  = 1;
                $pagination->count         = 0;
                $pagination->next_page     = null;
                $pagination->previous_page = null;

                $out             = new \stdClass;
                $out->results    = [];
                $out->pagination = $pagination;

                $app->debugger->endDBTask('findall');
                return $out;
            } else {
                $app->debugger->endDBTask('findall');
                return [];
            }
        } else {
            $class = get_class($this);

            foreach ($request as $doc) {
                $instance = new $class($this->collection->getName());

                foreach ($doc as $key => $value) {
                    if (is_array($value)) {
                        $instance->{$key} = $value;
                    } elseif (is_object($value) && !stristr(get_class($value), 'Mongo')) {
                        $instance->{$key} = new \stdClass;

                        foreach ($value as $k => $v) {
                            $instance->{$key}->{$k} = $v;
                        }
                    } else {
                        $instance->{$key} = $value;
                    }
                }

                $results[] = $instance;
            }

            /* If we paginate */
            if ($this->query['paginate']) {
                $request = $this->collection->find($this->query['where'], $this->query['select']);

                $total_pages = ceil($request->count() / $this->query['limit']);
                $page        = ($this->query['offset'] + $this->query['limit']) / $this->query['limit'];

                if ($this->query['offset'] == 0) {
                    $page = 1;
                }

                $current_page  = floor($page);
                $next_page     = ($page < $total_pages) ? $page + 1 : '';
                $previous_page = ($page - 1 > 0) ? $page - 1 : '';

                $pagination                = new \stdClass;
                $pagination->total_pages   = $total_pages;
                $pagination->current_page  = $current_page;
                $pagination->count         = $total_pages;
                $pagination->next_page     = $next_page;
                $pagination->previous_page = $previous_page;

                $out             = new \stdClass;
                $out->results    = $results;
                $out->pagination = $pagination;

                $app->debugger->endDBTask('findall');

                return $out;
            } else {
                $app->debugger->endDBTask('findall');
                return $results;
            }
        }
    }

    /**
     *
     * Find a document by it's id
     *
     * @param   mixed   id
     * @return  mixed   Model object if we have a it, null otherwise
     * @access  public
     * @final
     *
     */
    public final function findById($id)
    {
        return $this->where(['_id' => new \MongoId($id)])->find();
    }

    /**
     *
     * A static version of findById
     *
     * @param   mixed   id
     * @return  mixed   Model object if we have a it, null otherwise
     * @access  public
     * @static
     * @final
     *
     */
    public static final function withId($id)
    {
        $class    = get_called_class();
        $instance = new $class;

        return $instance->where(['_id' => new \MongoId($id)])->find();
    }

    /**
     *
     * Run the aggregate method with the give array of data
     * (project, match, order, limit, etc.)
     *
     * @param   array   array of arguments
     * @return  array   the aggregate result
     * @access  public
     * @final
     *
     */
    public final function aggregate($aggregate)
    {
        $app = Application::$instance;
        $app->debugger->startDBTask('aggregate');
        self::$dbCalls++;
        $result = $this->collection->aggregate($aggregate);
        $app->debugger->endDBTask('aggregate');

        return $result;
    }

    /**
     *
     * Count how many documents are in the collection
     *
     * @param   array   where statement
     * @return  int     the count
     * @access  public
     * @final
     *
     */
    public final function count($by=null)
    {
        $app = Application::$instance;
        $app->debugger->startDBTask('count');
        self::$dbCalls++;
        $result = $this->collection->count($by);
        $app->debugger->endDBTask('count');

        return $result;
    }

    /**
     *
     * Destroy the record or destroy records based on a query
     *
     * @param   bool    destroy all that match?
     * @access  public
     * @final
     *
     */
    public final function destroy($all=false)
    {
        $app = Application::$instance;
        $app->debugger->startDBTask('destroy');

        $options = ($all == false) ? ['justOne' => true] : [];

        $app->events->trigger(Event::DB_DESTROY_QUERY, $this->query['where']);
        $this->collection->remove($this->query['where'], $options);

        self::$dbCalls++;
        $app->debugger->endDBTask('destroy');
    }

    /**
     *
     * Destroy a record by it's id
     *
     * @param   mixed   string or mongoId object
     * @access  public
     * @final
     *
     */
    public final function destroyById($id)
    {
        $app = Application::$instance;
        $app->debugger->startDBTask('destroy');

        if (is_string($id)) {
            $id = new \MongoId($id);
        }

        $app->events->trigger(Event::DB_DESTROY_BYID, (string)$id);
        $this->collection->remove(['_id' => $id]);
        self::$dbCalls++;

        $app->debugger->endDBTask('destroy');
    }

    /**
     *
     * Drop the current collection
     *
     * @access  public
     * @final
     *
     */
    public final function drop()
    {
        $app = Application::$instance;
        $app->debugger->startDBTask('drop');

        $app->events->trigger(Event::DB_DROP_COLLECTION, $this->collection->getName());
        $this->collection->drop();
        self::$dbCalls++;

        $app->debugger->endDBTask('drop');
    }

    /**
     *
     * Insert or update a record
     *
     * @param   string      the update key to look for
     * @return  \MongoId    the mongo id that is created or updated
     * @access  public
     * @final
     *
     */
    public final function save($key='_id')
    {
        $app = Application::$instance;
        $app->debugger->startDBTask('save');

        self::$dbCalls++;

        if (empty($this->{$key})) {
            /* Add */
            foreach ($this as $var => $value) {
                if ($var != 'connection' && $var != 'query' && $var != 'collection' && $var != 'isGridFS' && $var != 'app') {
                    $query[$var] = $value;
                }
            }

            $query['creation_date']     = new \MongoDate(time());
            $query['modification_date'] = new \MongoDate(time());

            $this->collection->insert($query);

            $clone      = $this;
            $clone->_id = $query['_id'];
            $app->events->trigger(Event::DB_INSERT, $clone);

            $app->debugger->endDBTask('save');
            return $query['_id'];
        } else {
            /* Update */
            foreach ($this as $var => $value) {
                if ($var != '_id' && $var != 'connection' && $var != 'query' && $var != 'collection' && $var != 'isGridFS' && $var != $key && $var != 'creation_date' && $var != 'modification_date') {
                    $query[$var] = $value;
                }
            }

            $query['modification_date'] = new \MongoDate(time());
            $keyval = null;

            if ($key == '_id') {
                $keyval = $this->{$key};

                if (!is_object(($this->{$key}))) {
                    $keyval = new \MongoId($this->{$key});
                }
            }

            $where = [$key => $keyval];
            $this->collection->update($where, ['$set' => $query]);

            $app->events->trigger(Event::DB_UPDATE, $this);

            $app->debugger->endDBTask('save');
            return $this->_id;
        }
    }

    /**
     *
     * create an index on given field
     *
     * @param   string  the name of the field
     * @param   int     the direction (1 or -1)
     * @param   array   list of options
     * @access  public
     * @static
     * @final
     *
     */
    public static final function createIndex($field, $sorting=-1, $options=[])
    {
        $app = Application::$instance;
        $app->debugger->startDBTask('createindex');
        self::$dbCalls++;
        self::getCollectionInstance(get_called_class())->createIndex([$field => $sorting], $options);
        $app->debugger->endDBTask('createindex');
    }

    /**
     *
     * create a compound index on given fields
     *
     * @param   array   list of fields with their directions
     * @param   array   list of options
     * @access  public
     * @static
     * @final
     *
     */
    public static final function createCompoundIndex($list, $options=[])
    {
        $app = Application::$instance;
        $app->debugger->startDBTask('compoundindex');
        self::$dbCalls++;
        self::getCollectionInstance(get_called_class())->createIndex($list, $options);
        $app->debugger->endDBTask('compoundindex');
    }

    /**
     *
     * Delete the given field's index (if exists)
     *
     * @param   mixed   string for simple index, array for compound index
     * @access  public
     * @static
     * @final
     *
     */
    public static final function deleteIndex($field)
    {
        $app = Application::$instance;
        $app->debugger->startDBTask('deleteindex');
        self::$dbCalls++;
        self::getCollectionInstance(get_called_class())->deleteIndex($field);
        $app->debugger->endDBTask('deleteindex');
    }

    /**
     *
     * Delete all indexes from the collection
     *
     * @access  public
     * @static
     * @final
     *
     */
    public static final function deleteIndexes()
    {
        $app = Application::$instance;
        $this->app->debugger->startDBTask('deleteindex');
        self::$dbCalls++;
        self::getCollectionInstance(get_called_class())->deleteIndexes();
        $this->app->debugger->endDBTask('deleteindex');
    }

    /**
     *
     * Create a db reference for the given document id
     *
     * @param   mixed   mongoId object or id string
     * @return  mixed   document object if is valid, null otherwise
     * @access  public
     * @static
     * @final
     *
     */
    public static final function reference($id)
    {
        $app = Application::$instance;
        $app->debugger->startDBTask('reference');
        self::$dbCalls++;

        if (is_string($id)) {
            $id = new \MongoId($id);
        }

        $collection = self::getCollectionInstance(get_called_class());
        $document   = $collection->findOne(['_id' => $id]);
        $result     = $collection->createDBRef($document);

        $app->debugger->endDBTask('reference');
        return $result;
    }

    /**
     *
     * Get the document from the reference
     *
     * @param   mixed   array or object of a reference
     * @return  object  the referenced object
     * @access  public
     * @static
     * @final
     *
     */
    public static final function getReference($element)
    {
        $app = Application::$instance;
        $app->debugger->startDBTask('getreference');

        self::$dbCalls++;

        $collection = self::getCollectionInstance(get_called_class());

        $doc      = $collection->getDBRef((array)$element);
        $class    = get_called_class();
        $instance = new $class;

        /* Set up values */
        if (!empty($doc)) {
            foreach ($doc as $key => $value) {
                if (is_array($value)) {
                    $instance->{$key} = [];

                    foreach ($value as $k => $v) {
                        $instance->{$key}[$k] = $v;
                    }
                } elseif (is_object($value) && get_class($value) != 'MongoId') {
                    $instance->{$key} = new \stdClass;

                    foreach ($value as $k => $v) {
                        $instance->{$key}->{$k} = $v;
                    }
                } else {
                    $instance->{$key} = $value;
                }
            }
        }

        $app->debugger->endDBTask('getreference');
        return $instance;
    }

    /**
     *
     * Store a file in mongodb's GridFS
     *
     * Metadata is saved the same way a regular model works ($model->my_meta = ...)
     *
     * @param   mixed   the file path, the binary data or the upload filename
     * @param   bool    is it a file path
     * @param   bool    is it an upload (needs to match with file to work (ex: true and true))
     * @oaran   string  the optional information about file's mime type
     * @return  string  the file id
     * @access  public
     * @final
     *
     */
    public final function addFile($file, $is_file=true, $is_upload=false, $mime=null)
    {
        $app = Application::$instance;
        $app->debugger->startDBTask('addfile');

        self::$dbCalls++;

        $query = [];

        $query['mime'] = $mime;
        foreach ($this as $var => $value) {
            if ($var != 'connection' && $var != 'query' && $var != 'collection' && $var != 'isGridFS') {
                $query[$var] = $value;
            }
        }

        if ($is_file) {
            if ($is_upload) {
                $id = $this->collection->storeUpload($file, $query);
            } else {
                $id = $this->collection->storeFile($file, $query);
            }
        } else {
            $id = $this->collection->storeBytes($file, $query);
        }

        $app->events->trigger(Event::DB_GRID_INSERT, $id);
        $app->debugger->endDBTask('addfile');

        return $id;
    }

    /**
     *
     * Get a file by querying for it
     *
     * @return  \MongoGridFSFile|null    the gridfs object or null
     * @access  public
     * @final
     *
     */
    public final function getFile()
    {
        $app = Application::$instance;
        $app->debugger->startDBTask('getfile');
        self::$dbCalls++;

        $result = $this->collection->findOne($this->query['where'], []);

        $app->debugger->endDBTask('getfile');

        if (!empty($result)) {
            return $result;
        } else {
            return null;
        }
    }

    /**
     *
     * Convenience method for getFile (get by it's id)
     *
     * @param   mixed                   string or mongoId object
     * @return  \MongoGridFSFile|null   the gridfs object or null
     * @access  public
     * @final
     *
     */
    public final function getFileById($id)
    {
        self::$dbCalls++;

        if (is_string($id)) {
            $id = new \MongoId($id);
        }

        return $this->where(['_id' => $id])->getFile();
    }

    /**
     *
     * Delete a file by it's id
     *
     * @param   mixed   string or mongoId object
     * @access  public
     * @final
     *
     */
    public final function destroyFileById($id)
    {
        $app = Application::$instance;
        $app->debugger->startDBTask('destroyfile');
        self::$dbCalls++;

        if (is_string($id)) {
            $id = new \MongoId($id);
        }

        $app->events->trigger(Event::DB_GRID_DESTROY_BYID, (string)$id);
        $this->collection->remove(['_id' => $id]);
        $app->debugger->endDBTask('destroyfile');
    }

    /**
     *
     * Delete a/many file(s) by a query
     *
     * @param   bool    delete just 1 file matching the query
     * @access  public
     * @final
     *
     */
    public final function destroyFiles($just_one=true)
    {
        $app = Application::$instance;
        $app->debugger->startDBTask('destroyfiles');
        self::$dbCalls++;

        $app->events->trigger(Event::DB_GRID_DESTROY_QUERY, $this->query['where']);
        $this->collection->remove($this->query['where'], ['justOne' => $just_one]);
        $app->debugger->endDBTask('destroyfiles');
    }

    /**
     *
     * Disconnect from server
     *
     * @access  public
     * @static
     * @final
     *
     */
    public static final function disconnect()
    {
        if (self::$connection) {
            self::$connection->close(true);
        }
    }

    /**
     *
     * Specify that this model is GridFS model
     *
     * @param   bool    true/false
     * @access  public
     * @final
     *
     */
    public final function gridFS($is=false)
    {
        $this->isGridFS = true;
    }

    /**
     *
     * Get the total count of db calls made yet
     *
     * @return  int     the number
     * @access  public
     * @final
     *
     */
    public static final function getTotalCalls()
    {
        return self::$dbCalls;
    }
}
