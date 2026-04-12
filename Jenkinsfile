pipeline {
    agent any
    environment {
        REGISTRY = 'yassineshili'
        IMAGE = 'vetcare'
        TAG = "${BUILD_NUMBER}"
        K8S_NAMESPACE = 'default'
    }
    stages {
        stage('Checkout') { steps { checkout scm } }
        
        stage('Lint') {
            steps {
                sh 'docker run --rm -v $PWD:/app composer:2 install --no-dev'
                sh 'docker run --rm -v $PWD:/app php:8.2-cli php bin/console lint:container'
            }
        }
        
        stage('Build & Push') {
            steps {
                withCredentials([usernamePassword(credentialsId: 'docker-hub-creds', usernameVariable: 'DOCKER_USER', passwordVariable: 'DOCKER_PASS')]) {
                    sh 'echo $DOCKER_PASS | docker login -u $DOCKER_USER --password-stdin'
                    sh 'docker build -t $REGISTRY/$IMAGE:$TAG .'
                    sh 'docker tag $REGISTRY/$IMAGE:$TAG $REGISTRY/$IMAGE:latest'
                    sh 'docker push $REGISTRY/$IMAGE:$TAG'
                    sh 'docker push $REGISTRY/$IMAGE:latest'
                }
            }
        }
        stage('Wait for MySQL') {
            steps {
                sh 'kubectl wait --for=condition=available deployment/mysql --timeout=180s'
                sh 'sleep 10'  
            }
        }   

        
        stage('Migrate DB') {
            steps {
                sh """
                  kubectl apply -f k8s/job-migrate.yaml -n $K8S_NAMESPACE
                  kubectl wait --for=condition=complete job/vetcare-migrate -n $K8S_NAMESPACE --timeout=120s
                """
            }
        }
        
        stage('Deploy to k3s') {
            steps {
                sh 'kubectl apply -f k8s/configmap.yaml -f k8s/secret.yaml -f k8s/deployment.yaml -f k8s/service.yaml -n $K8S_NAMESPACE'
                sh 'kubectl rollout status deployment/vetcare -n $K8S_NAMESPACE --timeout=60s'
            }
        }
    }
    post {
        failure {
            echo '❌ Deployment failed. Check: kubectl logs -l app=vetcare'
        }
        success {
            echo '✅ Deployed! Access at: http://<WSL-IP>:30080 or http://localhost:30080 (Windows)'
        }
    }
}