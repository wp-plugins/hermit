<div class="wrap">
    <div class="ht-wrap" ng-app="HTApp">
        <!-- 菜单开始 -->
        <div ng-controller="MenuCtl as mc">
            <div class="ht-nav">
                <ul>
                    <li ng-repeat="menu in mc.menus track by $index">
                        <a ng-class="{current: mc.HTAppLocation.current == menu.url}" href="#{{menu.url}}">{{menu.title}}<span class="ht-nav-count">{{menu.count}}</span></a>
                    </li>
                    <li><a href="javascript:;" ng-click="mc.catLayerShow()">+ 新建分类</a></li>
                </ul>
            </div>
            <div class="ht-layer" ng-show="mc.newLayer">
                <div class="ht-layer-cat-content ht-layer-content">
                    <div class="ht-layer-header">
                        <span class="ht-layer-title">新建分类</span>
                        <span class="ht-layer-close ht-layer-close-icon dashicons-before" ng-click="mc.catLayerHide()"></span>
                    </div>
                    <div class="ht-layer-body">
                        <div class="ht-layer-item">
                            <div class="ht-layer-label">
                                <label for="ht-layer-newcat">请输入一个新的分类名称：</label>
                            </div>
                            <input id="ht-layer-newcat" class="ht-layer-input" type="text" ng-model="mc.newCatTitle" required/>
                            <div ng-show="mc.newCatTitleError" class="ht-layer-error">{{mc.newCatTitleError}}</div>
                        </div>
                    </div>
                    <div class="ht-layer-footer">
                        <div class="btn-group">
                            <div class="btn" ng-click="mc.newCatClick()">创建</div>
                        </div>
                        <div class="btn-group">
                            <div class="btn ht-layer-close" ng-click="mc.catLayerHide()">取消</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- 菜单结束 -->

        <!-- 音乐库开始 -->
        <div class="ht-main" ng-controller="MainCtl">
            <div class="ht-main-body" ng-view></div>
        </div>
        <!-- 音乐库结束 -->

        <!-- 提示层开始 -->
        <div class="ht-alert" ng-class="ac.HTAppAlert.getAlert().status" ng-controller="AlertCtl as ac">
            <div class="animate-switch-container" ng-switch on="ac.HTAppAlert.getAlert().status">
                <div class="animate-switch" ng-switch-when="loading">
                    <span class="ht-alert-icon"><span class="icon-autorenew"></span></span>
                    <span class="ht-alert-message" ng-bind="ac.HTAppAlert.getAlert().message"></span>
                </div>
                <div class="animate-switch" ng-switch-when="success">
                    <span class="ht-alert-icon"><span class="icon-done"></span></span>
                    <span class="ht-alert-message" ng-bind="ac.HTAppAlert.getAlert().message"></span>
                </div>
                <div class="animate-switch" ng-switch-when="err">
                    <span class="ht-alert-icon"><span class="icon-warning"></span></span>
                    <span class="ht-alert-message" ng-bind="ac.HTAppAlert.getAlert().message"></span>
                </div>
                <div class="animate-switch" ng-switch-default></div>
            </div>
        </div>
        <!-- 提示层结束 -->
    </div>
</div>