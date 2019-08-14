InvestorBundle
=====

# Setup

```
composer require sam/investor-bundle:dev-master
```

1. Add in `sam.yml` the block `sam_investor`, please view [configuration](#configuration) part for more infos.
2. In `security.yml` add roles, please see [Roles](#roles) section.
3. Run doctrine migrations (create a new one if doesn't exist).
4. Add in your `routing.yml` : 
```
sam_investor:
    resource: "@SAMInvestorBundle/Resources/config/routing.yml"
    prefix:   /
```
5. Add in your `AppKernel.php`:
```
new SAM\InvestorBundle\SAMInvestorBundle(),
```
6. Add in `web/js/app.js`:
```
require('../../vendor/sam/investor-bundle/Resources/public/js/app.js')
```
7. Add in `web/css/main.scss`:
```
@import "../../vendor/sam/investor-bundle/Resources/public/css/app";
```

# Infos

## Configuration

Default configuration:

```
sam_investor:
    sam_investor:
        analytics:
            investment_amount_range_points: [500, 1000, 3000, 5000, 10000, 15000]
        investment:
            min: 0
            max: 30000
```

## Roles

This is roles that we used inside the bundle:

* ROLE_INVESTOR_ADMIN
* ROLE_INVESTOR_USER

Into `role_hierarchy` you need to add:

1. Add to `ROLE_USER` the role `ROLE_INVESTOR_USER`
2. Add to `ROLE_ADMIN` the role `ROLE_INVESTOR_ADMIN`
3. Add a new line:
```
ROLE_INVESTOR_ADMIN: ROLE_INVESTOR_USER
```

## Settings

All settings are set under the category `bundle.investor`. To manage them go to `/admin/lexxpavlov/settings/settings/list`

| Key             | Description                                    |
|-----------------|------------------------------------------------|
| global.enable   | Activate bundle (add or remove investor link in `_user_nav.html.twig`) |