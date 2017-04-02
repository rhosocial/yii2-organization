# Using the Role-Based Authorization Control Features

## Preparation

We only provide database-based RBAC features. So you need to create the corresponding data tables followed by [yii2-user](https://github.com/rhosocial/yii2-user).

We provide the following six permissions:

- ManageMember
- ManageProfile
- RevokeDepartment
- RevokeOrganization
- SetUpDepartment
- SetUpOrganization

and four roles:

- DepartmentAdmin
- DepartmentCreator
- OrganizationAdmin
- OrganizationCreator

The relationship among them is shown below:

![Authority Structure](authority-structure.jpg)

where the box represents the `role`, and the arc square indicates permission,
arrows represent inheritance, and arrows point to the target of succession.
