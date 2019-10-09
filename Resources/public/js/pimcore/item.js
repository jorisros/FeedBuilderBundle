pimcore.registerNS("feedbuilder.panelitem");

feedbuilder.panelitem = Class.create({
    initialize: function (data, parentPanel) {
        this.parentPanel = parentPanel;
        this.data = data;
        this.addLayout();
    },
    onRadioNodeChange: function(item){
        Ext.getCmp('selectionFieldsetJoris'+this.data.id).hide();
    },
    onRadioNodeClick: function(item){
        Ext.getCmp('feedbuilder_feed_overview-'+this.data.id).hide();

        var activeRadio  = null;
        for(var i =0; i<item.items.items.length; i++){
            if(item.items.items[i].checked){
                if(item.items.items[i].inputValue === 3){
                    Ext.getCmp('feedbuilder_feed_overview-'+this.data.id).show();
                }
            }
        }

        return {};
    },
    radioEvent: function() {
      return {
          change : this.onRadioNodeClick.bind(this),
         // afterrender : this.onRadioNodeClick.bind(this)
          //afterlayout : this.onRadioNodeClick.bind(this)

        }
    },
    buildForm: function() {
        this.form = new Ext.form.FormPanel({
            border: false,
            items: [{
                xtype: "fieldset",
                itemId: "generalFieldset",
                title: t("feedbuilder_fieldset_title"),
                collapsible: false,
                defaults: {
                    width: 400
                },
                items: [{
                    xtype: "textfield",
                    name: "text",
                    value: this.data.text,
                    fieldLabel: t("feedbuilder_form_title"),
                    disabled: true
                    },
                    this.getChannelCombo(),
                    {
                        xtype: 'radiogroup',
                        fieldLabel: t('feedbuilder_form_type'),
                        id: "feedbuilder_feed_radiogroup-"+this.data.id,
                        // Arrange radio buttons into two columns, distributed vertically
                        columns: 1,
                        vertical: true,
                        items: this.data.configuration.type,
                        listeners: this.radioEvent()

                    },
                    {
                        xtype: "textfield",
                        name: "root",
                        value: this.data.configuration.root,
                        fieldLabel: t("feedbuilder_form_root")
                    },
                    this.getServiceField(),
                ]
            },{
                xtype: "fieldset",
                id: "feedbuilder_feed_overview-"+this.data.id,
                title: t("feedbuilder_feed_overview"),
                collapsible: false,
                defaults: {
                    width: 400
                },
                items: [this.getUrl()
                    //this.getUrl()
                ]
            },
                {
                    xtype: "fieldset",
                    itemId: "selectionFieldset",
                    title: t("feedbuilder_fieldset_selection_title"),
                    collapsible: false,
                    defaults: {
                        width: 400
                    },
                    items: [{
                        xtype: "textfield",
                        name: "ipaddress",
                        value: this.data.configuration.ipaddress,
                        fieldLabel: t("feedbuilder_form_ipaddress"),
                        disabled: false
                    },

                        this.getPathField(),
                        this.getIsPublished(),
                        this.getClasses(),
                        //this.getUrl()
                    ]
                }]
        });
        return this.form;
    },

    getServiceField : function() {
        if(!this.data.configuration.service){
            this.data.configuration.service = 'feedBuilderBundle.defaultFeedBuilderService';
        }

        return new Ext.form.TextField({
            xtype: "textfield",
            name: "service",
            value: this.data.configuration.service,
            fieldLabel: t("feedbuilder_service_id"),
        })
    },

    getUrl: function() {
        var url = '/feedbuilder/'+this.data.text;
        return new Ext.Panel({
            value: 'jorisje',
            border: false,
            disabled: false,
            html: '<b>Urls</b><br>JSON: <a href="'+url+'.json" target="_blank">'+url+'.json</a><br>XML: <a href="'+url+'.xml" target="_blank">'+url+'.xml</a><br>HTML: <a href="'+url+'.html" target="_blank">'+url+'.html</a><br>RAW: <a href="'+url+'.raw" target="_blank">'+url+'.raw</a>'
        });

    },
    getClasses: function () {

        var store = new Ext.data.JsonStore({
            storeId: 'myStore',
            autoLoad: true,
            proxy: {
                type: 'ajax',
                url: '/admin/feedbuilder/get-classes',
                reader: {
                    type: 'json',
                }
            }
        });

        return new Ext.form.ComboBox({
            name: 'class',
            fieldLabel: t('feedbuilder_form_classes'),
            valueField: 'id',
            displayField: 'text',
            renderTo: Ext.getBody(),
            triggerAction: 'all',
            store: store,
            value: this.data.configuration.class,
        });
    },
    getIsPublished: function() {
        return new Ext.form.Checkbox({
                fieldLabel: t("feedbuilder_form_published"),
                value: this.data.configuration.published,
                name: 'published'
            }
        );
    },
    getPathField: function() {
        if(!this.data.configuration.path){
            this.data.configuration.path = '/';
        }

        var href = {
            name: 'path',
            fieldLabel: t("feedbuilder_form_path"),
            value: this.data.configuration.path,
        };

        this.component = new Ext.form.TextField(href);

        this.component.on("render", function (el) {

            // add drop zone
            new Ext.dd.DropZone(el.getEl(), {
                reference: this,
                ddGroup: "element",
                getTargetFromEvent: function (e) {
                    return this.reference.component.getEl();
                },

                onNodeOver: function (target, dd, e, data) {

                    var record = data.records[0];
                    var data = record.data;

                    if(data.elementType == 'object') {
                        return Ext.dd.DropZone.prototype.dropAllowed;
                    }else{
                        return Ext.dd.DropZone.prototype.dropNotAllowed;
                    }

                }.bind(this),

                onNodeDrop: this.onNodeDrop.bind(this)
            });


            //el.getEl().on("contextmenu", this.onContextMenu.bind(this));

        }.bind(this));

        // disable typing into the textfield
        this.component.on("keyup", function (element, event) {
            element.setValue(this.data.path);
        }.bind(this));
        
        return this.component;
    },
    onNodeDrop: function (target, dd, e, data) {
        var record = data.records[0];
        var data = record.data;

        if(data.elementType == 'object') {
            this.component.setValue(data.path);
            return true;
        }else{
            return false;
        }
    },
    getChannelCombo: function() {

        var store = new Ext.data.JsonStore({
            storeId: 'myStore',
            autoLoad: true,
            proxy: {
                type: 'ajax',
                url: '/admin/feedbuilder/channel',
                reader: {
                    type: 'json',
                    rootProperty: 'list'
                }
            }
        });

        return new Ext.form.ComboBox({
            name: 'channel',
            fieldLabel: t('feedbuilder_form_channel'),
            valueField: 'abbr',
            displayField: 'name',
            renderTo: Ext.getBody(),
            triggerAction: 'all',
            store: store,
            value: this.data.configuration.channel,
        });
    },
    addLayout: function() {
        var panelButtons = [];
        var hideOverview = true;
        panelButtons.push('->',{
            text: t("delete"),
            iconCls: "pimcore_icon_delete",
            handler: this.delete.bind(this)
        },{
            text: t("save"),
            iconCls: "pimcore_icon_save_white",
            cls: "pimcore_save_button",
            scale: "small",
            handler: this.save.bind(this)
        });

        this.panel = new Ext.Panel({
            region: "center",
            id: "pimcore_feed_panel_" + this.data.id,
            labelWidth: 150,
            autoScroll: true,
            border: false,
            items: [
                this.buildForm()
                //this.getGeneralDefinitionPanel(),
                //this.getSourceDefinitionPanel(),
               // this.columnGrid,
                //this.getChartDefinitionPanel()
            ],
            bbar: panelButtons,
            title: this.data.text,
            bodyStyle: "padding: 20px;",
            closable: true,
            listeners: {
            //    afterrender: this.getColumnSettings.bind(this)
            }
        });

        this.parentPanel.getEditPanel().add(this.panel);
        this.parentPanel.getEditPanel().setActiveTab(this.panel);

        if (this.data.configuration && this.data.configuration.type && Array.isArray(this.data.configuration.type)) {
            this.data.configuration.type.forEach(function (item) {
                if (item.boxLabel === 'Feed' && item.checked === true) {
                    hideOverview = false;
                }
            });
        }

        if (hideOverview === true) {
            Ext.getCmp('feedbuilder_feed_overview-' + this.data.id).hide();
        }

        pimcore.layout.refresh();
    },
    save: function() {
        var allValues = this.form.getForm().getFieldValues();
        allValues.id = this.data.id;
        allValues.title = this.data.text;
        Ext.Ajax.request({
            url: "/admin/feedbuilder/save",
            method: "post",
            params: allValues,
            success: this.saveOnComplete.bind(this)
        });
        return {};
    },
    saveOnComplete: function () {
       // this.parentPanel.tree.getStore().load();
        pimcore.helpers.showNotification(t("success"), t("saved_successfully"), "success");
/*
        Ext.MessageBox.confirm(t("info"), t("reload_pimcore_changes"), function (buttonValue) {
            if (buttonValue == "yes") {
                window.location.reload();
            }
        }.bind(this));*/
    },
    delete: function() {
        var allValues = this.form.getForm().getFieldValues();
        allValues.id = this.data.id;
        allValues.title = this.data.text;

        Ext.Ajax.request({
            url: "/admin/feedbuilder/delete",
            method: "post",
            params: allValues,
            success: this.deleteOnComplete.bind(this)
        });

        var id = "pimcore_feed_panel_" + this.data.id;
        this.parentPanel.getEditPanel().remove( Ext.getCmp(id));

        return {};
    },
    deleteOnComplete: function () {
        this.parentPanel.tree.getStore().sync();
        this.parentPanel.tree.getStore().load();
        this.parentPanel.tree.getView().refresh();

        pimcore.helpers.showNotification(t("delete"), t("feedbuilder_delete_successfully"), "delete");
    }
});
