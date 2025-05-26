# General Contacts 

We should replace Contractor Contact Person with General Contacts + polimorphic relation to Contractor. 

## Contact model 
- first name: ?string
- last name: ?string
- position: ?string
- emails: ?jsonb - `[{ label, email }]`
- phone_numbers: ?jsonb - `[{ label, phone }]`
- notes: ?string 
- user_id: ?string - can be app user
- addresses[] - polimorphic relation 
- tags[] - polimorphic relation 

