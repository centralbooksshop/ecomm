define([
    'uiRegistry',
    'underscore',
    'Magento_Ui/js/form/element/abstract'
], function (uiRegistry, _, Abstract) {
    'use strict';

    return Abstract.extend({
        defaults: {
	    adminRoleUrl: '',
            adminRole: '',
	    currentUrl:'',
            imports: {
                updateDReadonly: '${ $.parentName }.vendor_id:value'
            }
        },

    
        updateDReadonly: function (value) {
		var adminRoleUrl = this.adminRoleUrl;
                    var self = this;
                    this.getAdminRole(adminRoleUrl).then(function(roleData) {
                             var adminRole = roleData.role;
                             var currentUrl = roleData.currentUrl;
			     self.adminRole = adminRole;
                             self.currentUrl = currentUrl;
			    var  newWord = self.addNewKeywordToUrl(currentUrl);
                           if(adminRole !== 'Administrators' &&  newWord !== true ){
                             var isValueEmpty = _.isEmpty(value);
                              if(isValueEmpty === false){
                                self.disabled(true);
                              }
                        }

                          }).catch(function(error) {
                                console.error('Error fetching admin role:', error);
                            })
        },
                        getAdminRole: function(adminRoleUrl) {
                            return new Promise(function(resolve, reject) {
                                fetch(adminRoleUrl, {
                                    method: 'GET',
                                    credentials: 'same-origin'
                                }).then(function(response) {
                                    return response.json();
                                }).then(function(data) {
                                    var adminRole = data.role;
                                    var currentUrl = window.location.href;
                                    resolve({ role: adminRole, currentUrl: currentUrl });

					//   resolve(adminRole);
                                }).catch(function(error) {
                                    reject(error);
                                });
                            });
                        },

        addNewKeywordToUrl: function(url) {

            var parsedUrl = new URL(url);
            var pathname = parsedUrl.pathname;
            var parts = pathname.split('/');
            var lastWord = parts[parts.length - 2];
                if (lastWord === 'new') {
                  return true;
                } else {
                  return false;
                } 
        }
    });
});

