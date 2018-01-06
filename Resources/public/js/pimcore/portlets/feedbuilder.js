
pimcore.registerNS("pimcore.layout.portlets.feedbuilder");
pimcore.layout.portlets.feedbuilder = Class.create(pimcore.layout.portlets.abstract, {

    getType: function () {
        return "pimcore.layout.portlets.feedbuilder";
    },


    getName: function () {
        return t("feedbuilder_name");
    },

    getIcon: function () {
        return "pimcore_icon_sql";
    },

    getLayout: function (portletId) {

        var defaultConf = this.getDefaultConfig();

        defaultConf.tools = [
            /*{
                type:'search',
                handler: this.openReport.bind(this)
            },*/
            {
                type:'gear',
                handler: this.editSettings.bind(this)
            },
            {
                type:'close',
                handler: this.remove.bind(this)
            }
        ];

        this.layout = Ext.create('Portal.view.Portlet', Object.extend(defaultConf, {
            title: this.getName(),
            iconCls: this.getIcon(),
            height: 275,
            layout: "fit",
            items: []
        }));

        //this.updateChart();

        this.layout.portletId = portletId;
        return this.layout;
    },
    configSelectionCombo:function() {
        return new Ext.form.ComboBox({
            xtype:"combo",
            width: 500,
            id: "pimcore_layout_portlets_feedbuilder",
            autoSelect: true,
            valueField: "id",
            displayField: "text",
            value: this.config,
            fieldLabel: t("feedbuilder_feed"),
            store: new Ext.data.Store({
                autoDestroy: true,
                autoLoad: true,
                proxy: {
                    type: 'ajax',
                    url: '/admin/feedbuilder/tree',
                    extraParams: {
                        portlet: 1
                    },
                    reader: {
                        type: 'json',
                        rootProperty: 'data'
                    }
                },
                listeners: {
                    load: function() {
                       // this.configSelectionCombo.setValue(this.config);
                    }.bind(this)
                },
                fields: ['id','text']
            }),
            triggerAction: "all"
        });
    },
    editSettings: function() {
        var win = new Ext.Window({
            width: 600,
            height: 150,
            modal: true,
            title: t('feedbuilder_name_settings'),
            closeAction: "destroy",
            items: [
                {
                    xtype: "form",
                    bodyStyle: "padding: 10px",
                    items: [
                        this.configSelectionCombo(),
                        {
                            xtype: "button",
                            text: t("save"),
                            handler: function () {
                                this.updateSettings();
                                win.close();
                            }.bind(this)
                        }
                    ]
                }
            ]
        });

        win.show();
    }
});
