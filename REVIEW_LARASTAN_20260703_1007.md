root@123ece5fd3b0:/var/www/html# composer larastan
The repository at "/var/www/html" does not have the correct ownership and git refuses to use it:

fatal: detected dubious ownership in repository at '/var/www/html'
To add an exception for this directory, call:

git config --global --add safe.directory /var/www/html

Note: Using configuration file /var/www/html/phpstan.neon.
 1152/1152 [▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓] 100%

 ------ --------------------------------------------------------------------------------------- 
  Line   app/Domain/Approval/Services/ApprovalResolutionService.php                             
 ------ --------------------------------------------------------------------------------------- 
  143    No error to ignore is reported on line 143.                                            
         🪪  ignore.unmatchedLine (non-ignorable)                                               
  144    Call to an undefined method Illuminate\Database\Eloquent\Relations\HasMany::active().  
         🪪  method.notFound                                                                    
  150    No error to ignore is reported on line 150.                                            
         🪪  ignore.unmatchedLine (non-ignorable)                                               
  151    Call to an undefined method Illuminate\Database\Eloquent\Relations\HasMany::active().  
         🪪  method.notFound                                                                    
  201    No error to ignore is reported on line 201.                                            
         🪪  ignore.unmatchedLine (non-ignorable)                                               
  202    Call to an undefined method Illuminate\Database\Eloquent\Relations\HasMany::active().  
         🪪  method.notFound                                                                    
 ------ --------------------------------------------------------------------------------------- 

 ------ ----------------------------------------------------------------------------- 
  Line   app/Domain/Approval/Services/WorkflowMatchingService.php                     
 ------ ----------------------------------------------------------------------------- 
  26     No error to ignore is reported on line 26.                                   
         🪪  ignore.unmatchedLine (non-ignorable)                                     
  28     Call to an undefined method Illuminate\Database\Eloquent\Builder::active().  
         🪪  method.notFound                                                          
  238    No error to ignore is reported on line 238.                                  
         🪪  ignore.unmatchedLine (non-ignorable)                                     
  240    Call to an undefined method Illuminate\Database\Eloquent\Builder::active().  
         🪪  method.notFound                                                          
  261    No error to ignore is reported on line 261.                                  
         🪪  ignore.unmatchedLine (non-ignorable)                                     
  263    Call to an undefined method Illuminate\Database\Eloquent\Builder::active().  
         🪪  method.notFound                                                          
 ------ ----------------------------------------------------------------------------- 

 ------ -------------------------------------------------------------------------------- 
  Line   app/Domain/Auth/Models/User.php                                                 
 ------ -------------------------------------------------------------------------------- 
  218    Call to an undefined static method Illuminate\Support\Facades\Auth::payload().  
         🪪  staticMethod.notFound                                                       
  219    Call to an undefined static method Illuminate\Support\Facades\Auth::payload().  
         🪪  staticMethod.notFound                                                       
 ------ -------------------------------------------------------------------------------- 

 ------ ------------------------------------------------------------------------------------------------------------------------------------- 
  Line   app/Domain/Common/Traits/HasIndexQuery.php (in context of class App\Domain\Admin\Contractors\Controllers\AdminContractorController)  
 ------ ------------------------------------------------------------------------------------------------------------------------------------- 
  59     Call to an undefined method Illuminate\Database\Eloquent\Builder<Illuminate\Database\Eloquent\Model>::getEloquentBuilder().          
         🪪  method.notFound                                                                                                                  
 ------ ------------------------------------------------------------------------------------------------------------------------------------- 

 ------ ------------------------------------------------------------------------------------------------------------------------------- 
  Line   app/Domain/Common/Traits/HasIndexQuery.php (in context of class App\Domain\Admin\Products\Controllers\AdminProductController)  
 ------ ------------------------------------------------------------------------------------------------------------------------------- 
  59     Call to an undefined method Illuminate\Database\Eloquent\Builder<Illuminate\Database\Eloquent\Model>::getEloquentBuilder().    
         🪪  method.notFound                                                                                                            
 ------ ------------------------------------------------------------------------------------------------------------------------------- 

 ------ ----------------------------------------------------------------------------------------------------------------------------- 
  Line   app/Domain/Common/Traits/HasIndexQuery.php (in context of class App\Domain\Auth\Controllers\MeActivityLogsController)        
 ------ ----------------------------------------------------------------------------------------------------------------------------- 
  59     Call to an undefined method Illuminate\Database\Eloquent\Builder<Illuminate\Database\Eloquent\Model>::getEloquentBuilder().  
         🪪  method.notFound                                                                                                          
 ------ ----------------------------------------------------------------------------------------------------------------------------- 

 ------ ----------------------------------------------------------------------------------------------------------------------------- 
  Line   app/Domain/Common/Traits/HasIndexQuery.php (in context of class App\Domain\Common\Controllers\ActivityLogController)         
 ------ ----------------------------------------------------------------------------------------------------------------------------- 
  59     Call to an undefined method Illuminate\Database\Eloquent\Builder<Illuminate\Database\Eloquent\Model>::getEloquentBuilder().  
         🪪  method.notFound                                                                                                          
 ------ ----------------------------------------------------------------------------------------------------------------------------- 

 ------ ----------------------------------------------------------------------------------------------------------------------------- 
  Line   app/Domain/Common/Traits/HasIndexQuery.php (in context of class App\Domain\Common\Controllers\ContactController)             
 ------ ----------------------------------------------------------------------------------------------------------------------------- 
  59     Call to an undefined method Illuminate\Database\Eloquent\Builder<Illuminate\Database\Eloquent\Model>::getEloquentBuilder().  
         🪪  method.notFound                                                                                                          
 ------ ----------------------------------------------------------------------------------------------------------------------------- 

 ------ ----------------------------------------------------------------------------------------------------------------------------- 
  Line   app/Domain/Common/Traits/HasIndexQuery.php (in context of class App\Domain\Common\Controllers\CountryController)             
 ------ ----------------------------------------------------------------------------------------------------------------------------- 
  59     Call to an undefined method Illuminate\Database\Eloquent\Builder<Illuminate\Database\Eloquent\Model>::getEloquentBuilder().  
         🪪  method.notFound                                                                                                          
 ------ ----------------------------------------------------------------------------------------------------------------------------- 

 ------ ----------------------------------------------------------------------------------------------------------------------------- 
  Line   app/Domain/Common/Traits/HasIndexQuery.php (in context of class App\Domain\Common\Controllers\MeasurementUnitController)     
 ------ ----------------------------------------------------------------------------------------------------------------------------- 
  59     Call to an undefined method Illuminate\Database\Eloquent\Builder<Illuminate\Database\Eloquent\Model>::getEloquentBuilder().  
         🪪  method.notFound                                                                                                          
 ------ ----------------------------------------------------------------------------------------------------------------------------- 

 ------ ----------------------------------------------------------------------------------------------------------------------------- 
  Line   app/Domain/Common/Traits/HasIndexQuery.php (in context of class App\Domain\Common\Controllers\TagController)                 
 ------ ----------------------------------------------------------------------------------------------------------------------------- 
  59     Call to an undefined method Illuminate\Database\Eloquent\Builder<Illuminate\Database\Eloquent\Model>::getEloquentBuilder().  
         🪪  method.notFound                                                                                                          
 ------ ----------------------------------------------------------------------------------------------------------------------------- 

 ------ ------------------------------------------------------------------------------------------------------------------------------------- 
  Line   app/Domain/Common/Traits/HasIndexQuery.php (in context of class App\Domain\Contractors\Controllers\ContractorActivityLogController)  
 ------ ------------------------------------------------------------------------------------------------------------------------------------- 
  59     Call to an undefined method Illuminate\Database\Eloquent\Builder<Illuminate\Database\Eloquent\Model>::getEloquentBuilder().          
         🪪  method.notFound                                                                                                                  
 ------ ------------------------------------------------------------------------------------------------------------------------------------- 

 ------ ----------------------------------------------------------------------------------------------------------------------------- 
  Line   app/Domain/Common/Traits/HasIndexQuery.php (in context of class App\Domain\Contractors\Controllers\ContractorController)     
 ------ ----------------------------------------------------------------------------------------------------------------------------- 
  59     Call to an undefined method Illuminate\Database\Eloquent\Builder<Illuminate\Database\Eloquent\Model>::getEloquentBuilder().  
         🪪  method.notFound                                                                                                          
 ------ ----------------------------------------------------------------------------------------------------------------------------- 

 ------ ----------------------------------------------------------------------------------------------------------------------------- 
  Line   app/Domain/Common/Traits/HasIndexQuery.php (in context of class App\Domain\EDoreczenia\Controllers\CertificateController)    
 ------ ----------------------------------------------------------------------------------------------------------------------------- 
  59     Call to an undefined method Illuminate\Database\Eloquent\Builder<Illuminate\Database\Eloquent\Model>::getEloquentBuilder().  
         🪪  method.notFound                                                                                                          
 ------ ----------------------------------------------------------------------------------------------------------------------------- 

 ------ ----------------------------------------------------------------------------------------------------------------------------- 
  Line   app/Domain/Common/Traits/HasIndexQuery.php (in context of class App\Domain\EDoreczenia\Controllers\MessageController)        
 ------ ----------------------------------------------------------------------------------------------------------------------------- 
  59     Call to an undefined method Illuminate\Database\Eloquent\Builder<Illuminate\Database\Eloquent\Model>::getEloquentBuilder().  
         🪪  method.notFound                                                                                                          
 ------ ----------------------------------------------------------------------------------------------------------------------------- 

 ------ ----------------------------------------------------------------------------------------------------------------------------- 
  Line   app/Domain/Common/Traits/HasIndexQuery.php (in context of class App\Domain\Exchanges\Controllers\ExchangeRateController)     
 ------ ----------------------------------------------------------------------------------------------------------------------------- 
  59     Call to an undefined method Illuminate\Database\Eloquent\Builder<Illuminate\Database\Eloquent\Model>::getEloquentBuilder().  
         🪪  method.notFound                                                                                                          
 ------ ----------------------------------------------------------------------------------------------------------------------------- 

 ------ ----------------------------------------------------------------------------------------------------------------------------- 
  Line   app/Domain/Common/Traits/HasIndexQuery.php (in context of class App\Domain\Expense\Controllers\ExpenseController)            
 ------ ----------------------------------------------------------------------------------------------------------------------------- 
  59     Call to an undefined method Illuminate\Database\Eloquent\Builder<Illuminate\Database\Eloquent\Model>::getEloquentBuilder().  
         🪪  method.notFound                                                                                                          
 ------ ----------------------------------------------------------------------------------------------------------------------------- 

 ------ ----------------------------------------------------------------------------------------------------------------------------- 
  Line   app/Domain/Common/Traits/HasIndexQuery.php (in context of class App\Domain\Feeds\Controllers\FeedController)                 
 ------ ----------------------------------------------------------------------------------------------------------------------------- 
  59     Call to an undefined method Illuminate\Database\Eloquent\Builder<Illuminate\Database\Eloquent\Model>::getEloquentBuilder().  
         🪪  method.notFound                                                                                                          
 ------ ----------------------------------------------------------------------------------------------------------------------------- 

 ------ ----------------------------------------------------------------------------------------------------------------------------- 
  Line   app/Domain/Common/Traits/HasIndexQuery.php (in context of class App\Domain\Financial\Controllers\GtuCodeController)          
 ------ ----------------------------------------------------------------------------------------------------------------------------- 
  59     Call to an undefined method Illuminate\Database\Eloquent\Builder<Illuminate\Database\Eloquent\Model>::getEloquentBuilder().  
         🪪  method.notFound                                                                                                          
 ------ ----------------------------------------------------------------------------------------------------------------------------- 

 ------ ----------------------------------------------------------------------------------------------------------------------------- 
  Line   app/Domain/Common/Traits/HasIndexQuery.php (in context of class App\Domain\Financial\Controllers\VatRateController)          
 ------ ----------------------------------------------------------------------------------------------------------------------------- 
  59     Call to an undefined method Illuminate\Database\Eloquent\Builder<Illuminate\Database\Eloquent\Model>::getEloquentBuilder().  
         🪪  method.notFound                                                                                                          
 ------ ----------------------------------------------------------------------------------------------------------------------------- 

 ------ ----------------------------------------------------------------------------------------------------------------------------- 
  Line   app/Domain/Common/Traits/HasIndexQuery.php (in context of class App\Domain\Invoice\Controllers\InvoiceController)            
 ------ ----------------------------------------------------------------------------------------------------------------------------- 
  59     Call to an undefined method Illuminate\Database\Eloquent\Builder<Illuminate\Database\Eloquent\Model>::getEloquentBuilder().  
         🪪  method.notFound                                                                                                          
 ------ ----------------------------------------------------------------------------------------------------------------------------- 

 ------ ------------------------------------------------------------------------------------------------------------------------------- 
  Line   app/Domain/Common/Traits/HasIndexQuery.php (in context of class App\Domain\Products\Controllers\ProductActivityLogController)  
 ------ ------------------------------------------------------------------------------------------------------------------------------- 
  59     Call to an undefined method Illuminate\Database\Eloquent\Builder<Illuminate\Database\Eloquent\Model>::getEloquentBuilder().    
         🪪  method.notFound                                                                                                            
 ------ ------------------------------------------------------------------------------------------------------------------------------- 

 ------ ----------------------------------------------------------------------------------------------------------------------------- 
  Line   app/Domain/Common/Traits/HasIndexQuery.php (in context of class App\Domain\Products\Controllers\ProductController)           
 ------ ----------------------------------------------------------------------------------------------------------------------------- 
  59     Call to an undefined method Illuminate\Database\Eloquent\Builder<Illuminate\Database\Eloquent\Model>::getEloquentBuilder().  
         🪪  method.notFound                                                                                                          
 ------ ----------------------------------------------------------------------------------------------------------------------------- 

 ------ ----------------------------------------------------------------------------------------------------------------------------- 
  Line   app/Domain/Common/Traits/HasIndexQuery.php (in context of class App\Domain\Projects\Controllers\ProjectController)           
 ------ ----------------------------------------------------------------------------------------------------------------------------- 
  59     Call to an undefined method Illuminate\Database\Eloquent\Builder<Illuminate\Database\Eloquent\Model>::getEloquentBuilder().  
         🪪  method.notFound                                                                                                          
 ------ ----------------------------------------------------------------------------------------------------------------------------- 

 ------ ----------------------------------------------------------------------------------------------------------------------------- 
  Line   app/Domain/Common/Traits/HasIndexQuery.php (in context of class App\Domain\Projects\Controllers\ProjectStatusController)     
 ------ ----------------------------------------------------------------------------------------------------------------------------- 
  59     Call to an undefined method Illuminate\Database\Eloquent\Builder<Illuminate\Database\Eloquent\Model>::getEloquentBuilder().  
         🪪  method.notFound                                                                                                          
 ------ ----------------------------------------------------------------------------------------------------------------------------- 

 ------ ----------------------------------------------------------------------------------------------------------------------------- 
  Line   app/Domain/Common/Traits/HasIndexQuery.php (in context of class App\Domain\Projects\Controllers\TaskController)              
 ------ ----------------------------------------------------------------------------------------------------------------------------- 
  59     Call to an undefined method Illuminate\Database\Eloquent\Builder<Illuminate\Database\Eloquent\Model>::getEloquentBuilder().  
         🪪  method.notFound                                                                                                          
 ------ ----------------------------------------------------------------------------------------------------------------------------- 

 ------ ----------------------------------------------------------------------------------------------------------------------------- 
  Line   app/Domain/Common/Traits/HasIndexQuery.php (in context of class App\Domain\Projects\Controllers\TaskStatusController)        
 ------ ----------------------------------------------------------------------------------------------------------------------------- 
  59     Call to an undefined method Illuminate\Database\Eloquent\Builder<Illuminate\Database\Eloquent\Model>::getEloquentBuilder().  
         🪪  method.notFound                                                                                                          
 ------ ----------------------------------------------------------------------------------------------------------------------------- 

 ------ ----------------------------------------------------------------------------------------------------------------------------- 
  Line   app/Domain/Common/Traits/HasIndexQuery.php (in context of class App\Domain\Skills\Controllers\SkillCategoryController)       
 ------ ----------------------------------------------------------------------------------------------------------------------------- 
  59     Call to an undefined method Illuminate\Database\Eloquent\Builder<Illuminate\Database\Eloquent\Model>::getEloquentBuilder().  
         🪪  method.notFound                                                                                                          
 ------ ----------------------------------------------------------------------------------------------------------------------------- 

 ------ ----------------------------------------------------------------------------------------------------------------------------- 
  Line   app/Domain/Common/Traits/HasIndexQuery.php (in context of class App\Domain\Skills\Controllers\SkillController)               
 ------ ----------------------------------------------------------------------------------------------------------------------------- 
  59     Call to an undefined method Illuminate\Database\Eloquent\Builder<Illuminate\Database\Eloquent\Model>::getEloquentBuilder().  
         🪪  method.notFound                                                                                                          
 ------ ----------------------------------------------------------------------------------------------------------------------------- 

 ------ --------------------------------------------------------------------------------------------------------------------------------- 
  Line   app/Domain/Common/Traits/HasIndexQuery.php (in context of class App\Domain\Subscription\Controllers\SubscriptionPlanController)  
 ------ --------------------------------------------------------------------------------------------------------------------------------- 
  59     Call to an undefined method Illuminate\Database\Eloquent\Builder<Illuminate\Database\Eloquent\Model>::getEloquentBuilder().      
         🪪  method.notFound                                                                                                              
 ------ --------------------------------------------------------------------------------------------------------------------------------- 

 ------ ----------------------------------------------------------------------------------------------------------------------------- 
  Line   app/Domain/Common/Traits/HasIndexQuery.php (in context of class App\Domain\Template\Controllers\InvoiceTemplateController)   
 ------ ----------------------------------------------------------------------------------------------------------------------------- 
  59     Call to an undefined method Illuminate\Database\Eloquent\Builder<Illuminate\Database\Eloquent\Model>::getEloquentBuilder().  
         🪪  method.notFound                                                                                                          
 ------ ----------------------------------------------------------------------------------------------------------------------------- 

 ------ ----------------------------------------------------------------------------------------------------------------------------- 
  Line   app/Domain/Common/Traits/HasIndexQuery.php (in context of class App\Domain\Tenant\Controllers\TenantActivityLogController)   
 ------ ----------------------------------------------------------------------------------------------------------------------------- 
  59     Call to an undefined method Illuminate\Database\Eloquent\Builder<Illuminate\Database\Eloquent\Model>::getEloquentBuilder().  
         🪪  method.notFound                                                                                                          
 ------ ----------------------------------------------------------------------------------------------------------------------------- 

 ------ ----------------------------------------------------------------------------------------------------------------------------- 
  Line   app/Domain/Common/Traits/HasIndexQuery.php (in context of class App\Domain\Tenant\Controllers\TenantIntegrationController)   
 ------ ----------------------------------------------------------------------------------------------------------------------------- 
  59     Call to an undefined method Illuminate\Database\Eloquent\Builder<Illuminate\Database\Eloquent\Model>::getEloquentBuilder().  
         🪪  method.notFound                                                                                                          
 ------ ----------------------------------------------------------------------------------------------------------------------------- 

 ------ --------------------------------------------------------------------------------------- 
  Line   app/Domain/Tenant/Models/OrganizationUnit.php                                          
 ------ --------------------------------------------------------------------------------------- 
  137    No error to ignore is reported on line 137.                                            
         🪪  ignore.unmatchedLine (non-ignorable)                                               
  139    Call to an undefined method Illuminate\Database\Eloquent\Relations\HasMany::active().  
         🪪  method.notFound                                                                    
  205    No error to ignore is reported on line 205.                                            
         🪪  ignore.unmatchedLine (non-ignorable)                                               
  206    Call to an undefined method Illuminate\Database\Eloquent\Relations\HasMany::active().  
         🪪  method.notFound                                                                    
  225    No error to ignore is reported on line 225.                                            
         🪪  ignore.unmatchedLine (non-ignorable)                                               
  226    Call to an undefined method Illuminate\Database\Eloquent\Relations\HasMany::active().  
         🪪  method.notFound                                                                    
 ------ --------------------------------------------------------------------------------------- 

 ------ ------------------------------------------- 
  Line   app/Http/Controllers/HealthController.php  
 ------ ------------------------------------------- 
  100    Variable $url might not be defined.        
         🪪  variable.undefined                     
  120    Variable $url might not be defined.        
         🪪  variable.undefined                     
 ------ ------------------------------------------- 

 ------ -------------------------------------------------------------------------------- 
  Line   app/Http/Middleware/EnsureTwoFactorVerified.php                                 
 ------ -------------------------------------------------------------------------------- 
  22     Call to an undefined static method Illuminate\Support\Facades\Auth::payload().  
         🪪  staticMethod.notFound                                                       
 ------ -------------------------------------------------------------------------------- 

 ------ -------------------------------------------------------------------------------- 
  Line   app/Http/Middleware/IsInTenant.php                                              
 ------ -------------------------------------------------------------------------------- 
  13     Call to an undefined static method Illuminate\Support\Facades\Auth::payload().  
         🪪  staticMethod.notFound                                                       
 ------ -------------------------------------------------------------------------------- 

 ------ --------------------------------------------------------------------------------------- 
  Line   database/factories/ExpenseFactory.php                                                  
 ------ --------------------------------------------------------------------------------------- 
  181    Access to an undefined property Illuminate\Database\Eloquent\Model::$full_address.     
         🪪  property.notFound                                                                  
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property  
  181    Access to an undefined property Illuminate\Database\Eloquent\Model::$full_address.     
         🪪  property.notFound                                                                  
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property  
  258    Access to an undefined property Illuminate\Database\Eloquent\Model::$full_address.     
         🪪  property.notFound                                                                  
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property  
  258    Access to an undefined property Illuminate\Database\Eloquent\Model::$full_address.     
         🪪  property.notFound                                                                  
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property  
 ------ --------------------------------------------------------------------------------------- 

 ------ --------------------------------------------------------------------------------------- 
  Line   database/factories/InvoiceFactory.php                                                  
 ------ --------------------------------------------------------------------------------------- 
  176    Access to an undefined property Illuminate\Database\Eloquent\Model::$full_address.     
         🪪  property.notFound                                                                  
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property  
  176    Access to an undefined property Illuminate\Database\Eloquent\Model::$full_address.     
         🪪  property.notFound                                                                  
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property  
  186    Access to an undefined property Illuminate\Database\Eloquent\Model::$full_address.     
         🪪  property.notFound                                                                  
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property  
  260    Access to an undefined property Illuminate\Database\Eloquent\Model::$full_address.     
         🪪  property.notFound                                                                  
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property  
  260    Access to an undefined property Illuminate\Database\Eloquent\Model::$full_address.     
         🪪  property.notFound                                                                  
         💡  Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property  
 ------ --------------------------------------------------------------------------------------- 

 ------ ---------------------------------------------------------------------------------------------- 
  Line   tests/Feature/Domain/Feeds/FeedControllerTest.php                                             
 ------ ---------------------------------------------------------------------------------------------- 
  247    No error to ignore is reported on line 247.                                                   
         🪪  ignore.unmatchedLine (non-ignorable)                                                      
  248    Call to an undefined method Mockery\ExpectationInterface|Mockery\HigherOrderMessage::once().  
         🪪  method.notFound                                                                           
 ------ ---------------------------------------------------------------------------------------------- 

 ------ ---------------------------------------------------------------------------------------------- 
  Line   tests/Unit/Domain/Contractors/Jobs/ProcessContractorRegistryConfirmationJobTest.php           
 ------ ---------------------------------------------------------------------------------------------- 
  137    No error to ignore is reported on line 137.                                                   
         🪪  ignore.unmatchedLine (non-ignorable)                                                      
  139    Call to an undefined method Mockery\ExpectationInterface|Mockery\HigherOrderMessage::once().  
         🪪  method.notFound                                                                           
  154    No error to ignore is reported on line 154.                                                   
         🪪  ignore.unmatchedLine (non-ignorable)                                                      
  156    Call to an undefined method Mockery\ExpectationInterface|Mockery\HigherOrderMessage::once().  
         🪪  method.notFound                                                                           
  208    No error to ignore is reported on line 208.                                                   
         🪪  ignore.unmatchedLine (non-ignorable)                                                      
  210    Call to an undefined method Mockery\ExpectationInterface|Mockery\HigherOrderMessage::once().  
         🪪  method.notFound                                                                           
 ------ ---------------------------------------------------------------------------------------------- 

 ------ ---------------------------------------------------------------------------------------------- 
  Line   tests/Unit/Domain/Contractors/Services/ContractorRegistryConfirmationServiceTest.php          
 ------ ---------------------------------------------------------------------------------------------- 
  187    No error to ignore is reported on line 187.                                                   
         🪪  ignore.unmatchedLine (non-ignorable)                                                      
  188    Call to an undefined method Mockery\ExpectationInterface|Mockery\HigherOrderMessage::with().  
         🪪  method.notFound                                                                           
  193    No error to ignore is reported on line 193.                                                   
         🪪  ignore.unmatchedLine (non-ignorable)                                                      
  194    Call to an undefined method Mockery\ExpectationInterface|Mockery\HigherOrderMessage::with().  
         🪪  method.notFound                                                                           
  199    No error to ignore is reported on line 199.                                                   
         🪪  ignore.unmatchedLine (non-ignorable)                                                      
  200    Call to an undefined method Mockery\ExpectationInterface|Mockery\HigherOrderMessage::with().  
         🪪  method.notFound                                                                           
  205    No error to ignore is reported on line 205.                                                   
         🪪  ignore.unmatchedLine (non-ignorable)                                                      
  206    Call to an undefined method Mockery\ExpectationInterface|Mockery\HigherOrderMessage::with().  
         🪪  method.notFound                                                                           
  231    No error to ignore is reported on line 231.                                                   
         🪪  ignore.unmatchedLine (non-ignorable)                                                      
  232    Call to an undefined method Mockery\ExpectationInterface|Mockery\HigherOrderMessage::with().  
         🪪  method.notFound                                                                           
  322    No error to ignore is reported on line 322.                                                   
         🪪  ignore.unmatchedLine (non-ignorable)                                                      
  323    Call to an undefined method Mockery\ExpectationInterface|Mockery\HigherOrderMessage::with().  
         🪪  method.notFound                                                                           
  328    No error to ignore is reported on line 328.                                                   
         🪪  ignore.unmatchedLine (non-ignorable)                                                      
  329    Call to an undefined method Mockery\ExpectationInterface|Mockery\HigherOrderMessage::with().  
         🪪  method.notFound                                                                           
  431    No error to ignore is reported on line 431.                                                   
         🪪  ignore.unmatchedLine (non-ignorable)                                                      
  432    Call to an undefined method Mockery\ExpectationInterface|Mockery\HigherOrderMessage::with().  
         🪪  method.notFound                                                                           
  437    No error to ignore is reported on line 437.                                                   
         🪪  ignore.unmatchedLine (non-ignorable)                                                      
  438    Call to an undefined method Mockery\ExpectationInterface|Mockery\HigherOrderMessage::with().  
         🪪  method.notFound                                                                           
  443    No error to ignore is reported on line 443.                                                   
         🪪  ignore.unmatchedLine (non-ignorable)                                                      
  444    Call to an undefined method Mockery\ExpectationInterface|Mockery\HigherOrderMessage::with().  
         🪪  method.notFound                                                                           
  477    No error to ignore is reported on line 477.                                                   
         🪪  ignore.unmatchedLine (non-ignorable)                                                      
  478    Call to an undefined method Mockery\ExpectationInterface|Mockery\HigherOrderMessage::with().  
         🪪  method.notFound                                                                           
 ------ ---------------------------------------------------------------------------------------------- 

                                                                                                                        
 [ERROR] Found 93 errors                                                                                                
                                                                                                                        

Script ./vendor/bin/phpstan analyse --memory-limit=2G handling the larastan event returned with error code 1
