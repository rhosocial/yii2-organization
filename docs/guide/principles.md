# Principles

## Organization & It's Members

The organization is divided into two categories:

- Organization
- Department

The "Organization" represents an independent entity in real
life, so "Organization" is the top level among our extension.

Organizations can only set up departments, departments can
also open subordinate departments.

Organization and their subordinate departments will contain
several members, of which there are creator and administrators.

The creator (or the founder), as the name suggests, is the
person who created the organization or department.
This identity is fixed throughout the organization or
department's life cycle, and can not be transferred to other
members.
Therefore, there is only one creator in organization or
department.

The administrator has all permissions within the
organization or department, except to assign, revoke
administrator role and revoke his organization or department.
The administrator can be more than one, it's identity is not
fixed.

Only the creator can give and revoke the administrator, but
only if the user must be a member of the current organization
or department.

Creator and administrator can add and remove members, but the
administrator can not remove other administrators from the
organization or department.

## Permissions

By default, the user does not have permission to set up
organization.
As a result, the webmaster needs to use the console command
to give the user an "set up organization" permission.

The user who has the permission "set up organization" will
become the creator of the organization after opening it.
At the same time, he can also open the subordinate
departments, and the site user can also be added as a member
of the organization.

If the creator of the organization is not the creator or 
administrator of a department of its organization, the
organization's creator can not manipulate the department.

Further, if the creator or administrator of the parent is not
the creator or administrator of the subordinate department,
he can not directly manipulate the subordinate department.

If a user wants to view other organizations, they should be
given their `viewOrganization` permission.

All permissions and the corresponding rules, as well as each
assignments, are kept in the database. So, this extension
relies on [[\rhosocial\user\rbac\DbManager]].

## Limitation

The number of organizations or departments set up by the user
is limited.
Likewise, the number of members of an organization or
department has a ceiling.

By default, the user who has the permission "set up organization"
can open up to 10 organizations.
Each organization or department can have up to 50 subordinate
departments, and have up to 100 members, including the creator
and the administrators.

The above limitation can be individually modified.

## Secondary Development

You should follow the following principles if you want to develop it:

- Inherit the class we provided, instead of using it directly.
- Complicated database operations should be wrapped into transaction, in order
to make data consistency.
- Prepare more complicated authentication steps for dangerous operations, such
as "revoke organization", "add or remove member", "change option", "assign or revoke
Administrator", etc.
- If you want to modify the default database schemas, please
use migrations.
