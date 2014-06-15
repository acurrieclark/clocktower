Searching
=========

## Functions

### model::find_all()
finds all in the model

### model::find_by_id()
shortcut which returns a single row by id

### model::find_all($options)
finds all results matching the fields stipulated by $where, ordered by $order (see below)

### model::findby_field($options)
finds first result matching the fields stipulated by $where, ordered by $order (see below)

### model::count($options)
returns the number of rows for a particular query

## Options

`array('where' => array('column' => 'value'), 'order' => ('column' => 'ASC/DESC'))`

**The where array can contain multiple values**

### NULL values
where we are searching for NULL, the column includes the operator

> `where => array('email IS' => null)`
>
> `where => array('email IS NOT' => null)`

### fuzzy searching
we can fuzzy search by include ' LIKE' in the field and adding '%' appropriately to the value

> `where => array('name LIKE' => '%alex%')`
