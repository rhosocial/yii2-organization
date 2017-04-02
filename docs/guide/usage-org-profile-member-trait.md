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

## Set Up Organization

The organization is opened by the user, so we do not recommend you to instantiate
the organization class directly.

The organization needs to have `SetUpOrganization` permission, which does not
give this permission to any user by default. So, first of all you should give
the user the permission.

There are many specific ways to achieve, for example:

- Use the console command to give this permission to a user.
- Give this permission to the user at the time of registration and set the maximum number (you need to develop it yourself).

After the user has this permission, the following statement can be executed:

```php
$result = $user->setUpOrganization("name");
```

It returns `true` if execution succeeds.

If you want to know the organization you just set up, you can access `lastSetUpOrganization` property.

## Revoke Organization

Similiary, we do not recommend that you delete the organization record directly.

## Set Up Department

## Revoke Department
