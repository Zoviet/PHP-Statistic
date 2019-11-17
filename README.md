# ================================
# PHP statistical class
# ================================

## PHP class with base statistical methods

Abstracts: Сorrelation, asymmetry, frequency and variation analytics, trend search and approximation for variational and non variational series. 

## Usage


```php

// Initialize
$stat = new Statistic();

// At will: set max and min series lenght (2..9999 by default)
$stat->set_defaults(5,99);

// Demo test with random series: 20 - lenght

$stat->methods(20);

// Pull all public methods with documentation

$stat->methods();

```
