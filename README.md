# GCAdmin

GCAdmin is a project created by Gregory Cascales to reuse several types of administrations in different web projects.

The configuration will be mainly in the config/services.yaml file :
```
parameters:
    GCAdmin: [...]
```

## Important

it is necessary that each of your entities have this function :
```
public function getAllAttributes() {
    return get_object_vars($this);
}
```

Copyright ©2018 all rights reserved.
