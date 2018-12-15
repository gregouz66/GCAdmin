# GCAdmin

GCAdmin is a project created by Gregory Cascales to reuse several types of administrations in different web projects.

The configuration will be mainly in the config/services.yaml file :
```
parameters:
    GCAdmin: [...]
```

## Important

1. it is necessary that each of your entities have this function :
```php
<?php
public function getAllAttributes() {
    return get_object_vars($this);
}
```

2. The *vendor\symfony\twig-bridge\Resources\views\Form\form_table_layout.html.twig* file is important to import to the project because it allows displaying to display an entity attribute if it is in the "array" format.



**Copyright ©2018 all rights reserved.**
