# CreatissimoMattermostBundle
 Symfony3.4 Bundle for communicating with the Mattermost Chat
 
 Example configuration:
 
    creatissimo_mattermost:
        webhook: https://chat.xym.com/asdasd
        appname: "My App"
        username: "App Bot"
        channel: "exceptions"
        iconUrl: "https://www.xym.com/logo.png"
        environments:
            dev:
                appname: "Dev App Name"
                username: "Dev Bot"
                enable: true
                terminate:
                    enable: true
                    exclude_exitcode: [0]
                exception:
                    enable: true
                    trace: true
    
            prod:
                enable: true
                terminate:
                    enable: true
                    exclude_exitcode: [0]
                exception:
                    enable: true
                    trace: true
                    exclude_class:
                        - Symfony\Component\HttpKernel\Exception\NotFoundHttpException
