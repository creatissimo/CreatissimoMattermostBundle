# CreatissimoMattermostBundle
 Symfony2 Bundle for communicating with the Mattermost Chat
 
 Example configuration:
 
 
    creatissimo_mattermost:
     webhook: "https://chat.xyz.com/hooks/xxxxxxxxxxxxxx"
     botname: "Bot User"
     appname: "My App"
     channel: "exceptions"
     icon: "https://xyz.com/icon.png"
     environments:
         dev:
             enabled: true
             channel: "dev-exceptions"
         prod:
             exclude_exception:
                 - Symfony\Component\HttpKernel\Exception\NotFoundHttpException