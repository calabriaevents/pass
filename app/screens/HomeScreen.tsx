// app/screens/HomeScreen.tsx

import React, { useEffect, useState } from 'react';
import {
  StyleSheet,
  Text,
  View,
  FlatList,
  ActivityIndicator,
  TouchableOpacity,
} from 'react-native';
import type { NativeStackScreenProps } from '@react-navigation/native-stack';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { HomeStackParamList } from '../App'; // Importiamo i tipi da App.tsx

type Props = NativeStackScreenProps<HomeStackParamList, 'ArticleList'>;

interface Article {
  id: number;
  title: string;
  subtitle: string;
  category_name: string;
  city_name: string;
}

const API_URL = 'http://localhost:8000/api/get_articles.php';
const ARTICLES_CACHE_KEY = '@articles_list';

export function HomeScreen({ navigation }: Props): React.JSX.Element {
  const [isLoading, setLoading] = useState(true);
  const [articles, setArticles] = useState<Article[]>([]);
  const [isOffline, setOffline] = useState(false);

  const getArticles = async () => {
    // 1. Prova a caricare dalla cache all'avvio
    try {
        const cachedArticles = await AsyncStorage.getItem(ARTICLES_CACHE_KEY);
        if (cachedArticles !== null) {
            setArticles(JSON.parse(cachedArticles));
        }
    } catch (e) {
        console.log('Errore nel leggere la cache degli articoli:', e);
    }
    setLoading(false); // Rimuovi il caricamento iniziale dopo aver controllato la cache

    // 2. Prova a caricare dalla rete
    try {
      const response = await fetch(API_URL);
      if (!response.ok) throw new Error('Network response was not ok.');

      const json = await response.json();
      if (!json.success) throw new Error(json.error || 'API returned an error');

      setArticles(json.data);
      setOffline(false);

      // 3. Salva i nuovi dati nella cache
      await AsyncStorage.setItem(ARTICLES_CACHE_KEY, JSON.stringify(json.data));

    } catch (e) {
      console.log('Errore di rete, si utilizzano i dati in cache (se disponibili):', e);
      setOffline(true); // Imposta lo stato offline se la rete fallisce
    }
  };

  useEffect(() => {
    getArticles();
  }, []);

  const renderArticle = ({ item }: { item: Article }) => (
    <TouchableOpacity onPress={() => navigation.navigate('Detail', { articleId: item.id })}>
      <View style={styles.itemContainer}>
        <Text style={styles.itemTitle}>{item.title}</Text>
        <Text style={styles.itemSubtitle}>{item.subtitle}</Text>
        <Text style={styles.itemMeta}>{item.category_name} - {item.city_name}</Text>
      </View>
    </TouchableOpacity>
  );

  return (
    <View style={styles.container}>
      {isOffline && (
          <View style={styles.offlineBanner}>
              <Text style={styles.offlineText}>Sei offline. Stai visualizzando contenuti non aggiornati.</Text>
          </View>
      )}
      {isLoading ? (
        <ActivityIndicator size="large" color="#0000ff" />
      ) : (
        <FlatList
          data={articles}
          keyExtractor={({ id }) => id.toString()}
          renderItem={renderArticle}
          ListEmptyComponent={<Text style={styles.emptyText}>Nessun articolo da mostrare.</Text>}
        />
      )}
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
  },
  offlineBanner: {
    backgroundColor: '#ffcc00',
    padding: 10,
    alignItems: 'center',
  },
  offlineText: {
    color: '#000',
  },
  itemContainer: {
    backgroundColor: '#FFFFFF',
    padding: 20,
    marginVertical: 8,
    marginHorizontal: 10,
    borderRadius: 8,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.22,
    shadowRadius: 2.22,
    elevation: 3,
  },
  itemTitle: {
    fontSize: 18,
    fontWeight: 'bold',
  },
  itemSubtitle: {
    fontSize: 14,
    color: '#666',
    marginTop: 4,
  },
  itemMeta: {
    fontSize: 12,
    color: '#999',
    marginTop: 8,
    fontStyle: 'italic',
  },
  emptyText: {
      textAlign: 'center',
      marginTop: 50,
      fontSize: 16,
  }
});
