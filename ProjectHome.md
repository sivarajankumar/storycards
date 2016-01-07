This is a simple tool that allows a user to login and vote for diffrent features requests. It provides a limited number of votes for each user. Users can book mark featuers, view features in a list and search for features. Users can also create edit and delete features from the list. The page has access rights that allow the administrator to grant access to various features.

Server:
> The server is written PHP taking some advantage of PDOs and connects to a MySql database. It was developed on the xampp web stack. The server must run with notices turned off as the REST interface doesn't always receive all fields from the POSTs by design.

Web Client:

The web client is build on AngularJS and Bootstrap 2.3.2 utilizing some Bootstrap-UI components. The client accesses the sever via REST URL calls and interpenetrates the JSON responses.