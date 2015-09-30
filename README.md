# Laravel API Query Builder Package

Bu paket, uri parametrelerinden eloquent query oluşturur.

## Örnek

/api/users?age!=18&order=age,asc&limit=2&columns=name,age,city_id&includes=city

adresine yapılan sorguyu

Users::with(['city'])->select(['name', 'age', 'city_id'])->where('age', '!=', 18)->orderBy('age', 'asc')->take(2) olacak şekilde yorumlar.

## Diğer Kullanım Örnekleri

- /api/users?age<18
- /api/users?age<=18
- /api/users?age>18
- /api/users?age>=18
- /api/users?age!=18
- /api/users?age=18
- /api/users?columns=name,age,city_id
- /api/users?includes=city,country,town 

## Sabit Parametreler

### order

Sıralama için kullanılır, iki parametre alır. 

**Parametreler** (String column, String direction)

**Default** id,desc

**Örnek** ?order=id,desc

### limit

Kaç adet satır döndürüleceğini tanımlar

**Parametreler** (Integer limit)

**Default** 15

**Örnek** ?limit=5

### Page

Sayfalandırma parametresi

**Parametreler** (Integer page)

**Default** 1

**Örnek** ?page=3

### Columns

Sorguda getirilmesini istenen tablo sütunları

**Parametreler** (String columns)

**Default** *

**Örnek** ?columns=name,age,city_id,town_id,email

### Includes

Eager Loading. 

**Parametreler** (String includes)

**Default** []

**Örnek** ?includes=city,country,town


## Kurulum

İlk olarak composer.json dosyasına şu satırı ekleyin.

```
"require": {
  "unlu/laravel-api-query-builder": "*@dev"
}
```

Daha sonra terminalden şu komutu çalıştırın.

```
composer update
```

## Kullanım

``` php

namespace App\Whatever\Api\Controllers;

use App\Http\Requests;
use App\User;
use Illuminate\Http\Request;

// Add this line
use Unlu\Laravel\Api\QueryBuilder;

class UsersController extends Controller
{
  public function index(Request $request)
  {
    $queryBuilder = new QueryBuilder(new User, $request);
    
    return response->json([
      'data' => $queryBuilder->paginate()->result(),
      .
      .
    ]);
  }
}
```

## Methodlar

### ->paginate()

``` php
$queryBuilder = new QueryBuilder(new User, $request);

$queryBuilder->paginate()
```

Eğer url parametresinde "?limit" yoksa ön tanımlı olan 15'i limit değeri kabul eder ve 15'erli sayfalandırma yapar.
"?limit=2" şeklinde bir parametre varsa 2'şerli sayfalandırma yapar.

### ->get()

``` php
$queryBuilder = new QueryBuilder(new User, $request);

$queryBuilder->get()
```

paginate() methodunda olduğu gibi "limit" parametresi yoksa ön tanımlı olarak 15'i limit kabul ederek 15 sonuç döndürür.
"?limit=2" şeklinde bir parametre varsa 2 adet sonuç döner.

### ->result()

``` php
$queryBuilder = new QueryBuilder(new User, $request);

$queryBuilder->paginate()->result();
// or
$queryBuilder->get()->result();
```

`paginate()` ve `get()` methodlarından sonra oluşturulan sorgunun sonucuna ulaşmak için kullanır.

## Özel Filtreleme

İsterseniz tablo sütunlarına göre filtreleme yerine özel, karmaşık filtrelerinizi de kullanabilirsiniz.

**Unutmayın, bu paket url üzerinden gönderilen parametrelerde (sabit parametreler dışında) öncelikle sizin oluşturduğunuz
özel filtrenin varlığını kontrol eder. Eğer özel bir filtre oluşturduysanız onu uygular, özel filtre oluşturmadığınız taktirde
tablo sütununda filtreleme yapar.**

Örneğin ?city_id=34 sorgusuna direkt olarak tablodaki city_id sütunu değil de özel filtrenizin kullanılmasını isterseniz

şu şekilde yapabilirsiniz:

İlk olarak kullanıcağınız model için bir QueryBuilder sınıfından türeyen bir sınıf oluşturun.

Ardından filtreleme yapmak istediğiniz parametre ismini stadly case olacak şekilde "filterByParameterName"
methodu oluşturun.

``` php
<?php 
	use Unlu\Laravel\Api\QueryBuilder;

	class UserQueryBuilder extends QueryBuilder
	{
		public function filterByCityId($query, $id)
		{
			return $query->whereHas('city', function($q) use ($id) {
	            return $q->where('id', $id);
	        });
		}
	}
?>
```

Daha sonra controller içerisinde

`QueryBuilder` yerine oluşturduğunuz `UserQueryBuilder` sınıfını kullanın. Bu şekilde yaptığınız taktirde
url ile gelen city_id parametresinde oluşturduğunuz özel filtre uygulanır.

controller dosyası:

``` php
<?php 
	namespace App\Whatever\Api\Controllers;

	use App\Http\Requests;
	use App\User;
	use Illuminate\Http\Request;

	// Add this line
	use YourNameSpace\Path\UserQueryBuilder;

	class UsersController extends Controller
	{
	  public function index(Request $request)
	  {
	    $queryBuilder = new UserQueryBuilder(new User, $request);
	    
	    return response->json([
	      'data' => $queryBuilder->paginate()->result(),
	      .
	      .
	    ]);
	  }
	}
?>
```

### Özel filtre methodunda "operatore" (=, !=, <, <=, >, >=) erişmek

Eğer özel filtreniz içerisinde sadece "=" operatorunu kullanmayacaksanız ve url parametresinde hangi operatorun
kullandığına erişmek isterseniz methodunuzu şu şekilde güncelleyin

``` php
<?php 
	use Unlu\Laravel\Api\QueryBuilder;

	class UserQueryBuilder extends QueryBuilder
	{
		public function filterByCityId($query, $id, $operator)
		{
			return $query->whereHas('city', function($q) use ($id, $operator) {
	            return $q->where('id', $operator, $id);
	        });
		}
	}
?>
```