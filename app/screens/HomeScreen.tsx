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

// Definiamo i tipi per i parametri dello stack di navigazione
type RootStackParamList = {
  Home: undefined;
  Detail: { articleId: number };
};

type Props = NativeStackScreenProps<RootStackParamList, 'Home'>;

interface Article {
  id: number;
  title: string;
  subtitle: string;
  category_name: string;
  city_name: string;
}

const API_URL = 'http://localhost:8000/api/get_articles.php';

export function HomeScreen({ navigation }: Props): React.JSX.Element {
  const [isLoading, setLoading] = useState(true);
  const [articles, setArticles] = useState<Article[]>([]);
  const [error, setError] = useState<string | null>(null);

  const getArticles = async () => {
    try {
      const response = await fetch(API_URL);
      if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
      const json = await response.json();
      if (!json.success) throw new Error(json.error || 'API returned an error');
      setArticles(json.data);
    } catch (e) {
      setError(e instanceof Error ? e.message : 'An unknown error occurred');
    } finally {
      setLoading(false);
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
      {isLoading ? (
        <ActivityIndicator size="large" color="#0000ff" />
      ) : error ? (
        <Text style={styles.errorText}>Errore nel caricamento: {error}</Text>
      ) : (
        <FlatList
          data={articles}
          keyExtractor={({ id }) => id.toString()}
          renderItem={renderArticle}
        />
      )}
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    paddingHorizontal: 10,
  },
  itemContainer: {
    backgroundColor: '#FFFFFF',
    padding: 20,
    marginVertical: 8,
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
  errorText: {
    color: 'red',
    textAlign: 'center',
    marginTop: 20,
  },
});
