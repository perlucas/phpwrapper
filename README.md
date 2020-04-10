# A simple set of PHP wrappers for extending functionality

[![Generic badge](https://img.shields.io/badge/version-1.0.0-green.svg)](https://github.com/perlucas/phpwrapper) [![Generic badge](https://img.shields.io/badge/php->=5.4-green.svg)](https://github.com/perlucas/phpwrapper)

This library provides a set of generic wrapper classes that can be used for implementing Adapter, Proxy , Null Object, Data Transfer Object or Lazy Loading design patterns in PHP. Below is an explanation about each of these wrapper classes.

Contents:

- [Wrapper](#Wrapper)
- [SetterGetterWrapper](#SetterGetterWrapper)
- [MultipleInstancesWrapper](#MultipleInstancesWrapper)
- [GenericNullObject](#GenericNullObject)
- [GhostWrapper](#GhostWrapper)

## Wrapper

The `Wrapper` class is the most basic wrapper implementation providing mechanisms for augmenting the wrapee functionality. We must extend this class for using it on a wrapee (an object that is going to be wrapped).

First of all, we must implement the `getWrapeeClass` method by returning the wrapee class name. That is necessary since the wrapper instantiation checks wether the wrapee is a subtype (or type) of that class:

```php
use SimpleWrapper\Wrapper;

class ProductWrapper extends Wrapper
{
    protected function getWrapeeClass()
    {
        return "Product";
    }
}
```

Our wrapper is ready to use, we just need a `Product` instance that is going to be wrapped. Let's suppose our `Product` class is defined as:

```php
class Product
{
    protected $id;
    protected $name;
    protected $price;
    
    public function __construct($id, $name, $price)
    {
        $this->id = $id;
        $this->name = $name;
        $this->price = $price;
    }
    
    public function getName() {return $this->name;}
    public function getPrice() {return $this->price;}
    public function calculateCost($q) {return $this->price * $q;}
}
```

Then we can access the product's methods from the wrapper instance:

```php
$product = new Product(11, "Apple", 2.30);
$productWrapper = new ProductWrapper($product);

echo $productWrapper->getName(); // Apple

echo $productWrapper->calculateCost(10); // 23
```

We can extend the product functionality by re-implementing the product methods on the wrapper:

```php
use SimpleWrapper\Wrapper;

class ProductWrapper extends Wrapper
{
    protected function getWrapeeClass()
    {
        return "Product";
    }
    
    public function calculateCost($q)
    {
        return "The cost is: " . $this->wrapee->calculateCost($q);
    }
}
```

This piece of extra code results in:

```php
$product = new ProductWrapper(new Product(11, "Apple", 2.30));

echo $product->getName(); // Apple

echo $product->calculateCost(10); // The cost is: 23
```

Since all the wrapee methods can be invoked from the wrapper class, we can use the wrapper as a wrapee proxy.

## SetterGetterWrapper

The `SetterGetterWrapper` is an extension of the `Wrapper` class that can be used to access the setters and getters of the wrapee. 

We use it as we did wen using the `Wrapper` class:

```php
use SimpleWrapper\SetterGetterWrapper;

class ProductWrapper extends SetterGetterWrapper
{
    protected function getWrapeeClass()
    {
        return "Product";
    }
    
    public function calculateCost($q)
    {
        return "The cost is: " . $this->wrapee->calculateCost($q);
    }
}

//////////////////////////
$product = new ProductWrapper(new Product(11, "Apple", 2.30));

echo $product->getName(); // Apple

echo $product->calculateCost(10); // The cost is: 23
```

Let's say our `Product` class has some setters like follow:

```php
class Product
{
    protected $id;
    protected $name;
    protected $price;
    
    public function __construct($id, $name, $price)
    {
        $this->id = $id;
        $this->name = $name;
        $this->price = $price;
    }
    
    public function getName() {return $this->name;}
    public function getPrice() {return $this->price;}
    public function setName($n) {$this->name = $n;}
    public function setPrice($p) {$this->price = $p;}
    public function calculateCost($q) {return $this->price * $q;}
}
```

Then, we could access them from the wrapper by doing `setName()` or `getName`. Instead, the `SetterGetterWrapper` introduces the following feature for accessing the wrapee properties:

```php
$product = new ProductWrapper(new Product(11, "Apple", 2.30));

echo $product->name; // Apple

echo $product->calculateCost(10); // The cost is: 23

$product->price = 5.60;

echo $product->price; // 5.60
```

For accessing the property `$product->name`, there must exists a method called `getName` or `getname` defined on any of the wrapper or wrapee class, or the `name` property must be defined as `public` on the wrapee. This also applies wen doing `$product-><another_property> = <a_value>;`.

## MultipleInstancesWrapper

As we can infer, the `MultipleInstancesWrapper` can be used for wrapping/extending the wrapped objects' functionalities. This class can be used as a _DataTransferObject_ since allow us to access methods that can be defined on multiple objects.

Let's define the following class:

```php
class Person
{
    protected $id;
    protected $name;
    protected $age;
    
    public function __construct($id, $name, $age)
    {
        $this->id = $id;
        $this->name = $name;
        $this->age = $age;
    }
    
    public function getName(){return $this->name;}
    public function getAge(){return $this->age;}
    public function isOld(){return $this->age > 60;}
}
```

We can use the `MultipleInstancesWrapper` as is, since it is not an `abstract` class. However, a better practice is extending it so we can provide extra code for augmenting the generic capabilities of the wrapper class:

```php
use SimpleWrapper\MultipleInstancesWrapper;

class PPWrapper extends MultipleInstancesWrapper
{
    /**
     * @Override
     */
	protected validateWrapees(array $objects)
    {
        parent::validateWrapees($objects);
        // do extra validation, e.g: validate that the first object is a Person object
    }
}
```

`PPWrapper` stands for "person and product wrapper". We can use it as follows:

```php
$wrapper = new PPWrapper(
	new Person(111, 'Peter', 45),
    new Product(222, 'Apple', 5.60)
);

echo $wrapper->calculateCost(100); // 560

echo $wrapper->isOld(); // false
```

What happen if we try to access the `getName` method? That method is defined on both wrapped objects, so we need to specify which class will act as the provider for the method by implementing `getProviderClassForMethod` on the wrapper:

```php
use SimpleWrapper\MultipleInstancesWrapper;

class PPWrapper extends MultipleInstancesWrapper
{
    protected function getProviderClassForMethod($method)
    {
        if ($method === 'getName') {
            return 'Product';
        }
    }
}
///////////////////
$wrapper = new PPWrapper(
	new Person(111, 'Peter', 45),
    new Product(222, 'Apple', 5.60)
);

echo $wrapper->calculateCost(100); // 560

echo $wrapper->isOld(); // false

echo $wrapper->getName(); // Apple
```

Without the `getProviderClassForMethod` implementation, the wrapper just invokes the method on the first object that defines it.

## GenericNullObject

The `GenericNullObject` class provides the basics for implementing the _Null Object_ design pattern. First, we need to extend the class by defining the `getClassName` method:

```php
use SimpleWrapper\GenericNullObject;

class NullProduct extends GenericNullObject
{
    protected function getClassName()
    {
        return "Product";
    }
}
```

Now we are ready to use the null product implementation. By default, all its methods return `null`:

```php
$product = new NullProduct();

echo $product->getName(); // null

echo $product->getPrice(); // null
```

We can define our default return type for all the methods overriding the `getDefaultReturnType` method defined on `GenericNullObject`:

```php
use SimpleWrapper\GenericNullObject;

class NullProduct extends GenericNullObject
{
    protected function getClassName(){return "Product";}
    
    protected function getDefaultReturnType(){return false;}
}

/// -------------------
$product = new NullProduct();

echo $product->getName(); // false

echo $product->getPrice(); // false
```

Also, we can be more specific defining the concrete return type for each method name on the `getReturnTypes` method provided by `GenericNullObject`. There we must define an associative array like the following:

```php
use SimpleWrapper\GenericNullObject;

class NullProduct extends GenericNullObject
{
    protected function getClassName(){return "Product";}
    
	protected function getReturnTypes()
    {
        return [
          	'getName' => '',
            'getPrice' => 0
        ];
    }
}

/// -------------------
$product = new NullProduct();

echo $product->getName(); // ''

echo $product->getPrice(); // 0
```

## GhostWrapper

The `GhostWrapper` class let us implement the _lazy loading_ pattern on objects that can be expensive to load in memory. `GhostWrapper` is an extension of `SetterGetterWrapper`.

Let's define our `Product` class as follows:

```php
class Product
{
    protected $id;
    protected $name = null;
    protected $price = null;
    protected $vendors = null;
    
    public function __construct($id) {$this->id = $id;}
    
    public function getName() {return $this->name;}
    public function getPrice() {return $this->price;}
    public function fetchVendors() {return $this->vendors;}
    
    public function loadProductProperties()
    {
        // load properties from a data repository using the id
    }
    
    public function loadVendors()
    {
        // load vendors from data repository using the id
    }
}
```

The `Product` class is a _Ghost_. It can be in three states at a specific time: completed loaded, partially loaded or not loaded. By using the `GhostWrapper` class we can give some transparency to the product class users that shouldn't be conscious of the loading mechanics. 

We must implement various methods for extending the `GhostWrapper` class. The `wrapeeMethodLoaded` must return `true` if the wrapped object can execute the method. If it cannot, then the `loadWrapeeMethod` is called for set the wrapped object able to execute it. This same idea applies when defining the methods `wrapeeGetterLoaded`, `loadWrapeeGetter`. Let's see an example below:

```php
use SimpleWrapper\GhostWrapper;

class ProductGhost extends GhostWrapper
{
    protected function getWrapeeClass(){return 'Product';}
    
    protected function wrapeeMethodLoaded($method, $args)
    {
        if ($method === 'fetchVendors') 
            return $this->wrapee->fetchVendors() !== null;
        if ($method === 'getName') 
            return $this->wrapeeGetterLoaded('name');
        if ($method === 'getPrice')
            return $this->wrapeeGetterLoaded('price');
        return true;
    }
    
    protected function loadWrapeeMethod($method, $args)
    {
        if ($method === 'fetchVendors') 
            return $this->wrapee->loadVendors();
        return $this->wrapee->loadProductProperties();
    }
    
    protected function wrapeeGetterLoaded($property)
    {
        return $this->wrapee->{$property} !== null;
    }
    
    protected function loadWrapeeGetter($property)
    {
        return $this->wrapee->loadProductProperties();
    }
}
```

This basic implementation let us work with a product wrapper without worrying about the loading of the object data. By default, the setters for the object are always loaded (since we can always set a value to an object property), however we can override the methods `wrapeeSetterLoaded` and `loadWrapeeSetter` if needed.

Below we can see the ghost wrapper behavior in action:

```php
$product = new ProductGhost(
	new Product(111)
);

echo $product->name; // this line causes the loading of the name and price

print_r($product->fetchVendors()); // this line causes the loading of the vendors
```

The product is instantiated with just the id of the object. The calling on `$product->name` results in invoking  `wrapeeGetterLoaded('name')` that returns `false`. Since it is false, the `loadWrapeeGetter('name')` method is invoked. That results in the name and price properties being loaded.

This same logic applies when calling `fetchVendors`. In this case, the loading of the vendors is triggered on the `loadWrapeeMethod` method.
