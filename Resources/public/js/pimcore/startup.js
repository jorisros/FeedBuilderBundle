pimcore.registerNS("pimcore.plugin.FeedBuilderBundle");

pimcore.plugin.FeedBuilderBundle = Class.create(pimcore.plugin.admin, {


    getClassName: function () {
        return "pimcore.plugin.FeedBuilderBundle";
    },

    initialize: function (id, options) {
        this.id = intval(id);
        pimcore.plugin.broker.registerPlugin(this);
    },

    pimcoreReady: function (params, broker) {
        var user = pimcore.globalmanager.get('user');

        if (user.isAllowed('plugins')) {

            var importMenu = new Ext.Action({
                text: t('feedbuilder_text'),
                icon: '/bundles/feedbuilder/img/svg/multiple_outputs.svg',

                handler: this.getPanel()
            });

            layoutToolbar.settingsMenu.add(importMenu);
        }
    },

    getPanel: function () {

        //var editor = new pimcore.report.custom.panel();

        var editor = new feedbuilder.panel();
        if (!this.panel) {
            this.panel = new Ext.Panel({
                id: "feedbuilder_editor",
                title: t("feedbuilder_title"),
                icon: '/bundles/feedbuilder/img/svg/multiple_outputs.svg',
                layout: "fit",
                closable:true,
                items: [editor.getTabPanel()]
            });

            var tabPanel = Ext.getCmp("pimcore_panel_tabs");
            tabPanel.add(this.panel);
            tabPanel.setActiveItem("feedbuilder_editor");

            this.panel.on("destroy", function () {
                pimcore.globalmanager.remove("feedbuilder_editor");
            }.bind(this));

            pimcore.layout.refresh();
        }

        return this.panel;
    },






    open: function() {
        this.getTab();

    },
    getGrid: function() {

        var store = Ext.create('Ext.data.Store', {
            fields: ['name', 'email', 'phone'],
            data: [
                { 'name': 'Lisa',  "email":"lisa@simpsons.com",  "phone":"555-111-1224"  },
                { 'name': 'Bart',  "email":"bart@simpsons.com",  "phone":"555-222-1234" },
                { 'name': 'Homer', "email":"home@simpsons.com",  "phone":"555-222-1244"  },
                { 'name': 'Marge', "email":"marge@simpsons.com", "phone":"555-222-1254"  }
            ]
        });

        return {
            xtype: 'button',
            text: t('reload'),
            handler: function () {
                alert('jrois');
            }.bind(this),
            iconCls: "pimcore_icon_reload"
        };
    },

    getTabPanel: function () {

        if (!this.panel) {
            this.panel = new Ext.Panel({
                border: false,
                layout: "border",
                items: [this.getTree(), this.getEditPanel()]
            });

            pimcore.layout.refresh();
        }

        return this.panel;
    },
    getTab: function() {
        this.tabPanel = Ext.getCmp("pimcore_panel_tabs");

        var tabId = "def_" + this.id;

        this.tab = new Ext.Panel({
            id: tabId,
            title: t('feedbuilder_title'),
            closable: true,
            layout: "border",
            items: [
                new Ext.Panel({
                    border: false,
                    layout: "border",
                    items: [this.getTree(), this.getEditPanel()]
                })

            ],
            object: this,
            cls: "pimcore_class_" + 'ss',
            iconCls: ''
        });

        this.tab.on("activate", function () {
            this.tab.updateLayout();
            pimcore.layout.refresh();
        }.bind(this));

        this.tab.on("afterrender", function (tabId) {
            this.tabPanel.setActiveItem(tabId);
            //pimcore.plugin.broker.fireEvent("postOpenObject", this, "object");
        }.bind(this, tabId));


        this.tabPanel.add(this.tab);
        console.log(this.tabPanel);

        // recalculate the layout
        pimcore.layout.refresh();
    },
    getTree: function(){
        if (!this.tree) {
            var store = Ext.create('Ext.data.TreeStore', {
                autoLoad: false,
                autoSync: true,
                proxy: {
                    type: 'ajax',
                    url: '/admin/reports/custom-report/tree',
                    reader: {
                        type: 'json'
                    }
                },
                root: {
                    iconCls: "pimcore_icon_thumbnails"
                }
            });

            this.tree = new Ext.tree.TreePanel({
                store: store,
                region: "west",
                autoScroll:true,
                animate:false,
                containerScroll: true,
                width: 250,
                split: true,
                root: {
                    id: '0',
                    expanded: true
                },
                rootVisible: false,
                //listeners: this.getTreeNodeListeners(),
                tbar: {
                    items: [
                        {
                            text: t("add_custom_report"),
                            iconCls: "pimcore_icon_add",
                            handler: alert('test')
                        }
                    ]
                }
            });

            this.tree.on("render", function () {
                this.getRootNode().expand();
            });
        }

        return this.tree;
    },
    getEditPanel: function() {
        return {};
    }
});

var FeedBuilderBundlePlugin = new pimcore.plugin.FeedBuilderBundle();
