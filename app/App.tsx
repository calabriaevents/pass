// App.tsx

import React, { useEffect } from 'react';
import { NavigationContainer } from '@react-navigation/native';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import { createBottomTabNavigator } from '@react-navigation/bottom-tabs';
import { HomeScreen } from './screens/HomeScreen';
import { DetailScreen } from './screens/DetailScreen';
import { MapScreen } from './screens/MapScreen';
import { UploadScreen } from './screens/UploadScreen';
import {
  PermissionsAndroid,
  Platform,
  SafeAreaView,
  StatusBar,
  useColorScheme,
} from 'react-native';
import messaging from '@react-native-firebase/messaging';

// Logica per le notifiche
const API_REGISTER_TOKEN_URL = 'http://localhost:8000/api/register_push_token.php';

async function getToken() {
    try {
        const token = await messaging().getToken();
        console.log('Firebase Messaging Token:', token);

        await fetch(API_REGISTER_TOKEN_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ token: token, platform: Platform.OS }),
        });
    } catch (error) {
        console.error('Errore nel recupero o invio del token:', error);
    }
}

async function requestUserPermission() {
  if (Platform.OS === 'ios') {
    const authStatus = await messaging().requestPermission();
    const enabled =
      authStatus === messaging.AuthorizationStatus.AUTHORIZED ||
      authStatus === messaging.AuthorizationStatus.PROVISIONAL;

    if (enabled) {
      console.log('Authorization status (iOS):', authStatus);
      await getToken();
    }
  } else if (Platform.OS === 'android') {
    try {
        await PermissionsAndroid.request(PermissionsAndroid.PERMISSIONS.POST_NOTIFICATIONS);
        // Su Android il token viene generato indipendentemente dal permesso (pre-13)
        // o dopo averlo concesso (13+), quindi lo chiamiamo comunque.
        await getToken();
    } catch (err) {
        console.warn('Errore richiesta permesso Android:', err)
    }
  }
}

// --- Tipi per la Navigazione ---
export type HomeStackParamList = {
  ArticleList: undefined;
  Detail: { articleId: number };
};
export type RootTabParamList = {
  Home: undefined;
  Map: undefined;
  Upload: undefined;
};

const Stack = createNativeStackNavigator<HomeStackParamList>();
const Tab = createBottomTabNavigator<RootTabParamList>();

function HomeStack() {
  const isDarkMode = useColorScheme() === 'dark';
  return (
    <Stack.Navigator
      screenOptions={{
        headerStyle: { backgroundColor: isDarkMode ? '#222' : '#FFF' },
        headerTintColor: isDarkMode ? '#FFF' : '#000',
      }}
    >
      <Stack.Screen
        name="ArticleList"
        component={HomeScreen}
        options={{ title: 'Articoli Recenti' }}
      />
      <Stack.Screen
        name="Detail"
        component={DetailScreen}
        options={{ title: 'Dettaglio Articolo' }}
      />
    </Stack.Navigator>
  );
}

function App(): React.JSX.Element {
  const isDarkMode = useColorScheme() === 'dark';

  useEffect(() => {
    requestUserPermission();
  }, []);

  const backgroundStyle = {
    backgroundColor: isDarkMode ? '#333' : '#F3F3F3',
    flex: 1,
  };

  return (
    <SafeAreaView style={backgroundStyle}>
      <StatusBar barStyle={isDarkMode ? 'light-content' : 'dark-content'} />
      <NavigationContainer>
        <Tab.Navigator
          screenOptions={{
            tabBarStyle: { backgroundColor: isDarkMode ? '#222' : '#FFF' },
            tabBarActiveTintColor: '#D9232D',
            tabBarInactiveTintColor: 'gray',
          }}
        >
          <Tab.Screen
            name="Home"
            component={HomeStack}
            options={{ title: 'Articoli', headerShown: false }}
          />
          <Tab.Screen
            name="Map"
            component={MapScreen}
            options={{ title: 'Mappa' }}
          />
          <Tab.Screen
            name="Upload"
            component={UploadScreen}
            options={{ title: 'Carica Foto' }}
          />
        </Tab.Navigator>
      </NavigationContainer>
    </SafeAreaView>
  );
}

export default App;
