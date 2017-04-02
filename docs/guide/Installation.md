# Installation

## Migrations

If you want to use built-in tables, you can execute built-in migrations (Only fit for MySQL).
Or you can create tables referenced by our provided migrations' comments.

This extension relies on the `User` table provided by [yii2-user](https://github.com/rhosocial/yii2-user),
so you must first perform the migration to create the user table.

Then you can execute the following command in the console:
```
yii migrate/up --migrationPath=@vendor --migrationNamespaces=rhosocial\organization\migrations --interactive=0
```

> Note: In the linux system, you may need to add additional escape characters(`\`).

You may see the following tips:
~~~
Yii Migration Tool (based on Yii v2.0.11.2)

Total 3 new migrations to be applied:
	rhosocial\organization\migrations\m170327_031029_createOrganizationTable
	rhosocial\organization\migrations\m170327_071501_createOrganizationProfileTable
	rhosocial\organization\migrations\m170327_111508_createOrganizationMemberTable

*** applying rhosocial\organization\migrations\m170327_031029_createOrganizationTable
    > create table {{%organization}} ... done (time: 0.038s)
    > add primary key organization_guid_pk on {{%organization}} (guid) ... done (time: 0.015s)
    > create unique index organization_id_unique on {{%organization}} (id) ... done (time: 0.015s)
    > create index organization_created_at_normal on {{%organization}} (created_at) ... done (time: 0.012s)

*** applied rhosocial\organization\migrations\m170327_031029_createOrganizationTable (time: 0.099s)

*** applying rhosocial\organization\migrations\m170327_071501_createOrganizationProfileTable
    > create table {{%organization_profile}} ... done (time: 0.034s)
    > add primary key organization_guid_profile_pk on {{%organization_profile}} (guid) ... done (time: 0.013s)
    > add foreign key organization_profile_fk: {{%organization_profile}} (guid) references {{%organization}} (guid) ... done (time: 0.015s)
    > create index organization_nickname_normal on {{%organization_profile}} (nickname) ... done (time: 0.016s)
    > create index organization_name_normal on {{%organization_profile}} (name) ... done (time: 0.017s)

*** applied rhosocial\organization\migrations\m170327_071501_createOrganizationProfileTable (time: 0.104s)

*** applying rhosocial\organization\migrations\m170327_111508_createOrganizationMemberTable
    > create table {{%organization_member}} ... done (time: 0.043s)
    > add primary key organization_member_pk on {{%organization_member}} (guid) ... done (time: 0.018s)
    > add foreign key member_organization_fk: {{%organization_member}} (organization_guid) references {{%organization}} (guid) ... done (time: 0.012s)
    > add foreign key member_user_fk: {{%organization_member}} (user_guid) references {{%user}} (guid) ... done (time: 0.012s)
    > create unique index organization_member_unique on {{%organization_member}} (id,organization_guid) ... done (time: 0.016s)
    > create unique index organization_user_unique on {{%organization_member}} (organization_guid,user_guid) ... done (time: 0.019s)
    > create index member_created_at_normal on {{%organization_member}} (created_at) ... done (time: 0.012s)

*** applied rhosocial\organization\migrations\m170327_111508_createOrganizationMemberTable (time: 0.139s)

3 migrations were applied.

Migrated up successfully.
~~~

It means successful.

## Authorization

### DbManager (Only Supports MySQL)

We use the DbManager provided by yii2-user to implement role-based authorization control.(MySQL Only).

Before using the database manager, you need to perform the migrations:
```
yii migrate/up --migrationPath=@vendor --migrationNamespaces=rhosocial\organization\rbac\migrations --interactive=0
```

> Note: In the linux system, you may need to add additional escape characters(`\`).

Then, you will see the following tips:
```
Yii Migration Tool (based on Yii v2.0.11.2)

Total 1 new migration to be applied:
	rhosocial\organization\rbac\migrations\m170328_063048_insertPermissionsRolesAndRules

*** applying rhosocial\organization\rbac\migrations\m170328_063048_insertPermissionsRolesAndRules
*** applied rhosocial\organization\rbac\migrations\m170328_063048_insertPermissionsRolesAndRules (time: 0.276s)

1 migration was applied.

Migrated up successfully.
```
It means successful.
