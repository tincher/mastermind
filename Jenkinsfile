#!groovy​

pipeline {
    agent any
    stages {
        stage('Deploy'){
            steps {
                dir ('/var/www/html/mastermind'){
                    echo 'Deploying'
                    checkout scm
                }
            }
        }
    }
}
