//Angular Routes

var myApp = angular.module('myApp', ['ui.bootstrap']);

myApp.config(function ($routeProvider) {
    $routeProvider
        .when('/',
        {
            templateUrl: 'dashboard.html'
        })
        .when('/cardlist',
        {
            controller: 'PostsCtrlAjax',
            templateUrl: 'cardlist.html'
        })
        .when('/cardshow',
        {
            controller: 'MyCardCtrl',
            templateUrl: 'cardshow.html'
        })
        .when('/newcard',
        {
            controller: 'NewCardCtrl',
            templateUrl: 'newcard.html'
        })
        .when('/editcard/:name/:description/:id',
        {
            controller: 'EditCardCtrl',
            templateUrl: 'editcard.html'
        })
        .when('/login',
        {
            controller: 'LoginCtrlAjax',
            templateUrl: 'login.html'
        })
        .when('/register',
        {
            controller: 'RegisterCtrlAjax',
            templateUrl: 'register.html'
        })
        .when('/admin',
        {
            templateUrl: 'admin.html'
        }).when('/users',
        {
            templateUrl: 'users.html',
            controller: 'FeatureListCtrl'
        })
        .otherwise({redirectTo: '/'});
});

// Angular Controllers

////////// Nav bar controller

function navCtrl($scope, $http, $location) {
    $scope.sessionData = JSON.parse(sessionStorage.getItem('Object'));
    $scope.clickMeOff = function () {
        $http({method: 'POST', url: "api/userlogic.php?logout=" + UserString() + "&token=" + TokenString()}).success(function (data) {
            sessionStorage.clear();
            $location.path("/login");
        })
    };
}
myApp.controller('navCtrl', navCtrl);


function LoginCtrlAjax($scope, $http, $location) {
    $scope.clickMe = function () {
        sessionStorage.clear();
        $http({method: 'POST', url: 'api/userlogic.php?username=' + $scope.username + "&password=" + $scope.password }).success(function (data) {
            saveUserData(data);
            if (data.Session != null) {
                $location.path("/");
            } else {
                $scope.alerts = data;
            }
        })
    };
}
myApp.controller('LoginCtrlAjax', LoginCtrlAjax);

////////// Registration Controller

function RegisterCtrlAjax($scope, $http) {
    $scope.clickMe = function () {
        $http({method: 'POST', url: 'api/userlogic.php?username=' + $scope.username + "&password=" + $scope.password + "&compassword=" + $scope.compassword  }).success(function (data) {
            $scope.alerts = data;
        })
    };
}
myApp.controller('RegisterCtrlAjax', RegisterCtrlAjax);

////////// Log Off Controller


////////// New Card Controller

function NewCardCtrl($scope, $http) {
    $scope.owner = JSON.parse(sessionStorage.getItem('Object')).Session.username;

    $http({method: 'POST', url: 'api/usermanagementlogic.php?username=' + UserString() + '&token=' + TokenString() + '' }).success(function (data) {
        $scope.users = data;
    })

    $scope.setOwner = function (owner) {
        $scope.owner = owner;
    };

    $scope.clickMe = function () {
        var url = 'api/cardlogic.php?name=' + "&username=" + UserString() + '&token=' + TokenString() + '&name=' + $scope.cardname + "&description=" + $scope.carddescription + "&owner=" + $scope.owner + '';
        $http({method: 'POST', url: url }).success(function (data) {
            $scope.data = data;
        })
    };
}
myApp.controller('NewCardCtrl', NewCardCtrl);

////////// Edit Card Controller

function EditCardCtrl($scope, $routeParams, $http, $location) {
    $scope.model = {
        name: $routeParams.name,
        description: $routeParams.description,
        id: $routeParams.id
    }

    $scope.clickMe = function (post) {
        var url = 'api/cardlogic.php?name=' + "&username=" + UserString() + '&token=' + TokenString() + '&id=' + post.id + '&cardname=' + post.name + '&description=' + post.description + '&edit=true';
        $http({method: 'POST', url: url }).success(function (data) {
        })
        $location.path("/cardlist");
    };


}
myApp.controller('EditCardCtrl', EditCardCtrl);

////////// List Card Controller

function PostsCtrlAjax($scope, $http) {
    $scope.sessionData = JSON.parse(sessionStorage.getItem('Object'));
    $http({method: 'POST', url: 'api/cardlogic.php?username=' + UserString() + '&token=' + TokenString() + ''}).success(function (data) {
        $scope.posts = data; // response data
    }).error(function (data) {
            $scope.posts = data;
        });
    $scope.deleteMe = function (post) {

        var r = confirm("Are you sure you want to delete this card.");
        if (r == true) {
            var url = 'api/cardlogic.php?name=' + "&username=" + UserString() + '&token=' + TokenString() + '&delete=' + post.id + '';
            $http({method: 'POST', url: url }).success(function (data) {
                location.reload();
            })
        } else {
        }
    };
    $scope.markMe = function (post) {
        var url = 'api/cardlogic.php?name=' + "&username=" + UserString() + '&token=' + TokenString() + '&thiscard=' + post.id + '&mark=true';
        $http({method: 'POST', url: url }).success(function (data) {
            $scope.results = data; //
            alert(post.name + " Has been added to your favorites");
        })
    };
}
myApp.controller('PostsCtrlAjax', PostsCtrlAjax);

////////// User Controller

function FeatureListCtrl($scope, $http) {
    $http({method: 'POST', url: 'api/usermanagementlogic.php?username=' + UserString() + '&token=' + TokenString() + '' }).success(function (data) {
        $scope.posts = data;
    })

    $scope.setCreate = function(post){
        var url ='api/usermanagementlogic.php?username=' + UserString() + '&token=' + TokenString() + '&thisuser='+post.username + '&rightname=createcard'+'&value=' + post.createcard+'' ;

        $http({method: 'POST', url: url }).success(function(data) {
        })
    };
    $scope.setVote = function(post){
        var url ='api/usermanagementlogic.php?username=' + UserString() + '&token=' + TokenString() + '&thisuser='+post.username + '&rightname=voteforcard'+'&value=' + post.voteforcard+'' ;

        $http({method: 'POST', url: url }).success(function(data) {
        })
    };
    $scope.setLoad = function(post){
        var url ='api/usermanagementlogic.php?username=' + UserString() + '&token=' + TokenString() + '&thisuser='+post.username + '&rightname=loadcards'+'&value=' + post.loadcards+'' ;

        $http({method: 'POST', url: url }).success(function(data) {
        })
    };
    $scope.setEdit = function(post){
        var url ='api/usermanagementlogic.php?username=' + UserString() + '&token=' + TokenString() + '&thisuser='+post.username + '&rightname=rightsedit'+'&value=' + post.rightsedit+'' ;

        $http({method: 'POST', url: url }).success(function(data) {
        })
    };
}
myApp.controller('FeatureListCtrl', FeatureListCtrl);

//////////My Card Controller

function MyCardCtrl($scope, $http) {
    $scope.sessionData = JSON.parse(sessionStorage.getItem('Object'));
    var url = 'api/cardlogic.php?username=' + UserString() + '&token=' + TokenString() + '&loadmycard=' + UserString();
    $http({method: 'POST', url: url }).success(function (data) {
        $scope.posts = data; // response data
        $scope.max = 11;
        $scope.isReadonly = true;
        $scope.ratingStates = [
            {stateOn: 'icon-star', stateOff: 'icon-star-empty'},
            {stateOff: 'icon-off'}
        ];
    });
    $scope.voteMe = function (post) {
        var url = 'api/cardlogic.php?name=' + "&username=" + UserString() + '&token=' + TokenString() + '&thiscard=' + post.id + '&vote=true';
        $http({method: 'POST', url: url }).success(function (data) {
            $scope.voteresults = data; //
            post.votes = Number(data.votes);
        })
    };
    $scope.deleteMe = function (post) {
        var url = 'api/cardlogic.php?name=' + "&username=" + UserString() + '&token=' + TokenString() + '&delete=' + post.id + '';
        $http({method: 'POST', url: url }).success(function (data) {
            location.reload();
        })
    };
    $scope.unVoteMe = function (post) {
        var url = 'api/cardlogic.php?name=' + "&username=" + UserString() + '&token=' + TokenString() + '&thiscard=' + post.id + '&unvote=true';
        $http({method: 'POST', url: url }).success(function (data) {
            $scope.voteresults = data; //
            post.votes = Number(data.votes);
        })
    };

    $scope.unMarkMe = function (post) {
        var url = 'api/cardlogic.php?name=' + "&username=" + UserString() + '&token=' + TokenString() + '&thiscard=' + post.id + '&unmark=true';
        $http({method: 'POST', url: url }).success(function () {
            location.reload();
        })
    };
}
myApp.controller('MyCardCtrl', MyCardCtrl);

// functions

function saveUserData(data) {
    if (typeof(Storage) !== "undefined") {
        var Object = data;
        sessionStorage.setItem('Object', JSON.stringify(Object));
    } else {
        alert("Your browser does not support web storage.");
    }
}

function UserString() {
    var data = JSON.parse(sessionStorage.getItem('Object'));
    var userSession = data.Session;
    var username = userSession.username;
    return username;
}

function TokenString() {
    var data = JSON.parse(sessionStorage.getItem('Object'));
    var userSession = data.Session;
    var token = userSession.token;
    return token;
}