<?php
/**
 * Base class for Collections
 * 
 * @license MIT
 * @copyright 2012 Jasny
 */

/** */
namespace Social;

/**
 * A collection of entities.
 */
class Collection extends \ArrayObject
{
    /**
     * Social connection
     * @var Connection
     */
    protected $_connection;
    
    /**
     * Request object to fetch next page
     * @var object
     */
    protected $_nextPage;

    
    /**
     * Class constructor
     * 
     * @param Connection $connection
     * @param array      $data
     * @param object     $nextPage    Request object to fetch next page
     */
    public function __construct(Connection $connection, array $data=array(), $nextPage=null)
    {
        $this->_connection = $connection;
        
        if (isset($nextPage)) {
            $this->_nextPage = is_string($nextPage) ? (object)array('url' => $nextPage) : (object)$nextPage;
        }
        
        parent::__construct($data);
    }
    
    /**
     * Get iterator used for foreach loop.
     * 
     * @return CollectionIterator 
     */
    public function getIterator()
    {
        return new CollectionIterator($this);
    }

    
    /**
     * Get API connection.
     * 
     * @return Connection
     */
    public function getConnection()
    {
        return $this->_connection;
    }
    
    
    /**
     * Fetch the next page.
     * 
     * @return Collection
     */
    public function nextPage()
    {
        if (!isset($this->_nextPage)) return null;
        return $this->getConnection()->doRequest($this->_nextPage);
    }
    
    /**
     * Perform an action on all entities in the collection.
     * 
     * @param string $name
     * @param array  $arguments
     * @return Result
     */
    public function __call($name, $arguments)
    {
        // Prepare and execute
        $this->getConnection()->prepare();
        foreach ($this as $entity) {
            call_user_func_array(array($entity, $name), $arguments);
        }
        $data = $this->getConnection()->execute();
        
        if ($name == 'expand') return $this;
        
        // Process data
        $keys = array_keys($this);
        $result = new Result($this->getConnection());
        
        foreach ($data as $i=>$item) {
            $key = $keys[$i];
            $result[$this[$key]] = $item;
        }
        
        return $result;
    }
}
