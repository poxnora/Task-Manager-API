param acrName string = 'TaskAppACR'
param appImage string =  'tasks-img'
param postgresImage string =  'postgres-img'
param redisImage string =  'redis-img'
param location string = 'westeurope'

resource acr 'Microsoft.ContainerRegistry/registries@2024-11-01-preview' = {
  name: acrName
  location: location
  sku: {
    name: 'Basic' 
  }
  properties: {
    adminUserEnabled: true
  }
}

resource containerAppEnv 'Microsoft.App/managedEnvironments@2022-03-01' = {
  name: '${acrName}-env'
  location: location
  properties: {
    appLogsConfiguration: {
      destination: 'log-analytics'
      logAnalyticsConfiguration: {
        customerId: logAnalyticsWorkspace.properties.customerId
        sharedKey: logAnalyticsWorkspace.listKeys().primarySharedKey
      }
    }
  }
}

resource logAnalyticsWorkspace 'Microsoft.OperationalInsights/workspaces@2021-06-01' = {
  name: '${acrName}-logs'
  location: location
  properties: {
    sku: {
      name: 'PerGB2018'
    }
    retentionInDays: 30
  }
}

resource appContainer 'Microsoft.App/containerApps@2024-10-02-preview' = {
  name: '${toLower(acrName)}-app'
  location: location
  properties: {
    managedEnvironmentId: containerAppEnv.id
    configuration: {
      ingress: {
        external: true
        targetPort: 8080
      }
      registries: [
        {
          server: '${toLower(acrName)}.azurecr.io'
          username: acr.listCredentials().username
          passwordSecretRef: 'acr-password'
        }
      ]
      secrets: [
        {
          name: 'acr-password'
          value: acr.listCredentials().passwords[0].value
        }
      ]
    }
    template: {
      containers: [
        {
          name: 'app'
          image: appImage
          env: [
            {
              name: 'APP_ENV'
              value: 'prod'
            }
            {
              name: 'DATABASE_URL'
              value: 'pgsql://user:password@postgres:5432/task_manager'
            }
            {
              name: 'REDIS_HOST'
              value: 'redis'
            }
            {
              name: 'REDIS_PORT'
              value: '6379'
            }
          ]
          resources: {
            cpu: 0.25
            memory: '0.5Gi'
          }
        }
        {
          name: 'postgres'
          image: postgresImage
          env: [
            {
              name: 'POSTGRES_USER'
              value: 'user'
            }
            {
              name: 'POSTGRES_PASSWORD'
              value: 'password'
            }
            {
              name: 'POSTGRES_DB'
              value: 'task_manager'
            }
          ]
          resources: {
            cpu: 0.25
            memory: '0.5Gi'
          }
        }
        {
          name: 'redis'
          image: redisImage
          resources: {
            cpu: 0.25
            memory: '0.5Gi'
          }
        }
      ]
    }
  }
}