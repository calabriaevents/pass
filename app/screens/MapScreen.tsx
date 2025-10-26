// app/screens/MapScreen.tsx

import React, { useEffect, useState } from 'react';
import { StyleSheet, View, PermissionsAndroid, Platform, Text } from 'react-native';
import MapView, { Marker, PROVIDER_GOOGLE } from 'react-native-maps';
import Geolocation from '@react-native-community/geolocation';

// --- Placeholder per la configurazione della Mappa ---
// Per far funzionare le mappe su Android, è necessario:
// 1. Ottenere una API Key per Google Maps da https://console.cloud.google.com/
// 2. Aggiungere la chiave al file `android/app/src/main/AndroidManifest.xml`:
//    <application>
//        ...
//        <meta-data
//            android:name="com.google.android.geo.API_KEY"
//            android:value="LA_TUA_API_KEY"/>
//        ...
//    </application>
// ---

interface Location {
    id: number;
    name: string;
    type: 'article' | 'city';
    latitude: number;
    longitude: number;
}

const API_URL = 'http://localhost:8000/api/get_map_locations.php';

export function MapScreen(): React.JSX.Element {
    const [locations, setLocations] = useState<Location[]>([]);
    const [userLocation, setUserLocation] = useState<{latitude: number, longitude: number} | null>(null);

    // Funzione per richiedere il permesso di localizzazione
    const requestLocationPermission = async () => {
        if (Platform.OS === 'android') {
            try {
                const granted = await PermissionsAndroid.request(
                    PermissionsAndroid.PERMISSIONS.ACCESS_FINE_LOCATION,
                    {
                        title: 'Permesso di Localizzazione',
                        message: 'Passione Calabria ha bisogno di accedere alla tua posizione per mostrarti dove sei sulla mappa.',
                        buttonNeutral: 'Chiedi dopo',
                        buttonNegative: 'Cancella',
                        buttonPositive: 'OK',
                    },
                );
                if (granted === PermissionsAndroid.RESULTS.GRANTED) {
                    console.log('Permesso di localizzazione concesso');
                    getCurrentLocation();
                } else {
                    console.log('Permesso di localizzazione negato');
                }
            } catch (err) {
                console.warn(err);
            }
        } else {
            // Su iOS il permesso viene gestito automaticamente dalla libreria
             getCurrentLocation();
        }
    };

    // Funzione per ottenere la posizione corrente
    const getCurrentLocation = () => {
        Geolocation.getCurrentPosition(
            position => {
                setUserLocation({
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude,
                });
            },
            error => console.log(error),
            { enableHighAccuracy: true, timeout: 20000, maximumAge: 1000 },
        );
    }

    // Funzione per caricare i punti di interesse dalla nostra API
    const getLocations = async () => {
        try {
            const response = await fetch(API_URL);
            const json = await response.json();
            if (json.success) {
                // Converti le coordinate in numeri
                const formattedLocations = json.data.map((loc: any) => ({
                    ...loc,
                    latitude: parseFloat(loc.latitude),
                    longitude: parseFloat(loc.longitude),
                }));
                setLocations(formattedLocations);
            }
        } catch (error) {
            console.error('Errore nel caricamento delle location:', error);
        }
    }

    useEffect(() => {
        requestLocationPermission();
        getLocations();
    }, []);

    return (
        <View style={styles.container}>
            <MapView
                provider={PROVIDER_GOOGLE}
                style={styles.map}
                initialRegion={{
                    latitude: 39.0742, // Centro della Calabria
                    longitude: 16.2425,
                    latitudeDelta: 3.5,
                    longitudeDelta: 2.5,
                }}
                showsUserLocation={true} // Mostra il punto blu per l'utente
            >
                {locations.map(location => (
                    <Marker
                        key={`${location.type}-${location.id}`}
                        coordinate={{ latitude: location.latitude, longitude: location.longitude }}
                        title={location.name}
                        description={location.type === 'city' ? 'Città' : 'Articolo'}
                    />
                ))}
            </MapView>
        </View>
    );
}

const styles = StyleSheet.create({
    container: {
        ...StyleSheet.absoluteFillObject,
        justifyContent: 'flex-end',
        alignItems: 'center',
    },
    map: {
        ...StyleSheet.absoluteFillObject,
    },
});
