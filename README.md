# GCAdmin

GCAdmin is a project created by [Gregory Cascales](https://www.cascales.fr/) to reuse several types of administrations in different web projects.

The configuration will be mainly in the config/services.yaml file :
```
parameters:
    GCAdmin:
        menu:
            - { entity: 'Article', label: 'Articles', icon: 'fa fa-newspaper-o' }
            - { entity: 'User', label: 'Utilisateurs', icon: 'fas fa-chart-bar' }
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

##optional configurations
```
parameters:
    GCAdmin:
        dashboard:
            cube-statistics:
                - { type: '1', color: '1', entity: 'Article', label: 'Articles postés', attr-duration-sql: 'created_at', fewer-days: 5, icon: 'zmdi zmdi-account-o' }
```

| Syntax  | Description |   Value    |
|---------|-------------|------------|
| type  | chart type    | 1, 2, 3, 4 |
| color | container's background color| 1, 2, 3, 4 |
| entity | entity used by the statistics | [string] |
| label | label used for the total result | [string] |
| attr-duration-sql | attribute used for the time value of the statistic (name of your field in your database and not that of your entity) | [string] |
| fewer-days | limit of days difference between the first and last statistics | [integer] |
| icon | class name for the icon used | [string] |



**Copyright ©2018 all rights reserved.**
