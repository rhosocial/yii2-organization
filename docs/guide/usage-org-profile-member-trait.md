# Using the Organization, Profile, Member and UserOrganizationTrait

## Preparation

We strongly recommend that you declare your own Models, use trait in your `User`:

Organization:
```php
class Organization extends \rhosocial\organization\Organization
{
    public $profileClass = <Profile class>;
    public $memberClass = <Member class>;
    ...
}
```

Profile:
```php
class Profile extends \rhosocial\organization\Profile
{
    public $hostClass = <Organization class>;
    ...
}
```

Member:
```php
class Member extends \rhosocial\organization\Member
{
    public $hostClass = <Organization class>;
    public $memberUserClass = <User class>;
    ...
}
```

Use UserOrganizationTrait:
```php
use rhosocial\organization\UserOrganizationTrait;

class User extends \rhosocial\user\User
{
    use UserOrganizationTrait;
    public function init()
    {
        $this->memberClass = <Member class>;
        $this->organizationClass = <Organization class>;
        parent::init();
    }
}
```
