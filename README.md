# dto-export
Symfony + API Platform vendor for exporting API DTOs into other languages for front-end easy integration.

## Installation

### 1. Install the bundle
```bash
composer require owlnext-fr/dto-export
```

### 2. Configure twig paths
```yaml
# config/packages/twig.yaml
twig:
  #...
  paths:
    '%kernel.project_dir%/vendor/owlnext-fr/dto-export/templates': 'owlnext_fr.dto_export'
```

## Usage

### 1. Create a DTO class
```php
# src/Dto/Output/UserOutputDTO.php
<?php

namespace App\Dto\Output;

class UserOutputDTO {
    public int $id;
    public string $name;
}
```

### 2. Add the tag `app.exportable_dto` to your class

#### With annotation
```php
# src/Dto/Output/UserOutputDTO.php

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.exportable_dto')]
class UserOutputDTO {
    public int $id;
    public string $name;
}
```

#### With interface implementation
To avoid the use of the `AutoconfigureTag` annotation in each of your DTOs, you can create a `ExportableDTOInterface` interface and implement it in your DTOs.
```php
# src/Dto/Impl/ExportableDTOInterface.php

namespace App\Dto\Impl;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.exportable_dto')]
interface ExportableDTOInterface {}
```

```php
# src/Dto/Output/UserOutputDTO.php

use App\Dto\Impl\ExportableDTOInterface;

class UserOutputDTO implements ExportableDTOInterface {
    public int $id;
    public string $name;
}
```

#### handle array of something
For array of primitives, scalars and object, you will have to add another attribute to your field using an array.
It will help the exporter to know what type of data is in the array and transform it correctly.

Here is an example with an array of strings:
```php
# src/Dto/Output/UserOutputDTO.php

# ...
use OwlnextFr\DtoExport\Attribute\ListOf;

class UserOutputDTO implements ExportableDTOInterface {
    #...
    
    #[ListOf(type: 'string')]
    /** @var string[] $roles list roles for this user. */
    public array $roles;
}

```

Here is an example with an array of objects:
```php
# src/Dto/Output/UserOutputDTO.php

# ...
use OwlnextFr\DtoExport\Attribute\ListOf;
use App\DTO\Output\SkillOutputDTO;

class UserOutputDTO implements ExportableDTOInterface {
    #...
    
    #[ListOf(type: SkillOutputDTO::class)]
    /** @var SkillOutputDTO[] $roles list skills of this user. */
    public array $skills;
}

```

### 3. Export your DTOs

Basic usage:
```bash
symfony console dto:export ...
```

For dart language:
```bash
symfony console dto:export <path to export> --type=dart --project-name=<project name>
```

For typescript language:
```bash
symfony console dto:export <path to export> --type=typescript
```