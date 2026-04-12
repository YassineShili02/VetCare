pipeline {
    agent any
    environment {
        REGISTRY = 'yassineshili'
        IMAGE = 'vetcare'
        TAG = "${BUILD_NUMBER}"
        K8S_NAMESPACE = 'default'
    }
    stages {
        stage('Checkout') {
            steps {
                checkout scm
            }
        }
        
        stage('Lint') {
    steps {
        sh '''
            docker run --rm -v $PWD:/app composer:2 sh -c "
                git config --global --add safe.directory /app
                echo 'APP_ENV=prod' > /app/.env
                echo 'APP_SECRET=ci-lint-only' >> /app/.env
                echo 'DATABASE_URL=\"mysql://vetcare_app:AppPass%212024Secure@mysql:3306/vetcare?serverVersion=10.4&charset=utf8mb4\"' >> /app/.env
                composer install --no-dev --no-scripts &&
                php bin/console lint:container
            "
        '''
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
                sh 'kubectl wait --for=condition=available deployment/mysql -n $K8S_NAMESPACE --timeout=180s || echo "MySQL not found, skipping wait"'
                sh 'sleep 10'
            }
        }
        
      stage('Migrate DB') {
    steps {
        sh '''
            export KUBECONFIG=/etc/rancher/k3s/k3s.yaml
            
            kubectl apply -f k8s/configmap.yaml -f k8s/secret.yaml -n $K8S_NAMESPACE
            
            kubectl get configmap vetcare-config -n $K8S_NAMESPACE
            kubectl get secret vetcare-secrets -n $K8S_NAMESPACE
            
            kubectl delete job vetcare-migrate -n $K8S_NAMESPACE --ignore-not-found=true
            
            kubectl apply -f k8s/job-migrate.yaml -n $K8S_NAMESPACE
            kubectl wait --for=condition=complete job/vetcare-migrate -n $K8S_NAMESPACE --timeout=300s
        '''
    }
}

stage('Deploy to k3s') {
    steps {
        sh '''
            export KUBECONFIG=/etc/rancher/k3s/k3s.yaml
            kubectl apply -f k8s/configmap.yaml -f k8s/secret.yaml -f k8s/deployment.yaml -f k8s/service.yaml -n $K8S_NAMESPACE
            kubectl rollout status deployment/vetcare -n $K8S_NAMESPACE --timeout=60s
        '''
    }
}
    }
    post {
        failure {
            echo '❌ Deployment failed. Check: kubectl logs -l app=vetcare'
        }
        success {
            echo '✅ Deployed! Access at: http://localhost:30080'
        }
    }
}