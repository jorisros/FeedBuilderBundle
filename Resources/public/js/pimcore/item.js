pimcore.registerNS("feedbuilder.panelitem");

feedbuilder.panelitem = Class.create({
    initialize: function (data, parentPanel) {
        this.parentPanel = parentPanel;
        this.data = data;
        this.addLayout();
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
                        // Arrange radio buttons into two columns, distributed vertically
                        columns: 1,
                        vertical: true,
                        id: "colorgroup",
                        items: [
                            { boxLabel: t('feedbuilder_form_type_object'), name: 'type', inputValue: '1' },
                            { boxLabel: t('feedbuilder_form_type_export'), name: 'type', inputValue: '2'},
                            { boxLabel: t('feedbuilder_form_type_feed'), name: 'type', inputValue: '3' }
                        ],
                        tbar        : [
                            {
                                text    : 'setValue on RadioGroup',
                                handler : function () {
                                    form.child('radiogroup').setValue({
                                        rb : '2'
                                    });
                                }
                            }
                        ]
                    },
                    {
                        xtype: "textfield",
                        name: "root",
                        value: this.data.configuration.root,
                        fieldLabel: t("feedbuilder_form_root")
                    }

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
    getUrl: function() {
        return new Ext.Panel({
            value: 'jorisje',
            border: false,
            disabled: false,
            html: '<br><br>Url<br><a href="">some text here</a>'

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
        panelButtons.push('->',{
            text: t("delete"),
            iconCls: "pimcore_icon_delete",
            handler: this.save.bind(this)
        },{
            text: t("save"),
            iconCls: "pimcore_icon_apply",
            handler: this.save.bind(this)
        });

        this.panel = new Ext.Panel({
            region: "center",
            id: "pimcore_sql_panel_" + this.data.title,
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
    }

});