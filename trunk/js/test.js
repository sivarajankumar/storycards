var app = angular.module('myApp', ['ngGrid']);
app.controller('MyCtrl', function($scope) {

    $scope.myData = [{name: "Moroni", age: 50},
        {name: "Tiancum", age: 43},
        {name: "Jacob", age: 27},
        {name: "Nephi", age: 29},
        {name: "Enos", age: 34}];
    $scope.gridOptions = {
        data: 'myData',
        enableCellSelection: true,
        enableRowSelection: false,
        enableCellEdit: true,

    };

});

function FeatureListCtrl($scope, $http){
    $http({method: 'POST', url: 'api/usermanagementlogic.php?username=' + UserString() + '&token=' + TokenString()+'' }).success(function(data) {
        alert(JSON.stringify(data));
        $scope.myData = data.userrights;


    })

}
myApp.controller('FeatureListCtrl',FeatureListCtrl);




















function UserString() {
    var data = JSON.parse(localStorage.getItem('Object'));
    var userSession = data.Session;
    var username = userSession.username;
    return username;
}

function TokenString() {
    var data = JSON.parse(localStorage.getItem('Object'));
    var userSession = data.Session;
    var token = userSession.token;
    return token;
}