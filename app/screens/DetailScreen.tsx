// app/screens/DetailScreen.tsx

import React, { useEffect, useState } from 'react';
import { StyleSheet, Text, View, ScrollView, ActivityIndicator, Image } from 'react-native';
import type { NativeStackScreenProps } from '@react-navigation/native-stack';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { HomeStackParamList } from '../App';

type Props = NativeStackScreenProps<HomeStackParamList, 'Detail'>;

interface ArticleDetails {
  id: number;
  title: string;
  subtitle: string;
  content: string;
  featured_image: string;
}

const API_BASE_URL = 'http://localhost:8000/api/get_article_details.php';
const ARTICLE_DETAIL_CACHE_PREFIX = '@article_detail_';

const getImageUrl = (imagePath: string) => {
    if (!imagePath || imagePath.startsWith('http')) return imagePath;
    return `http://localhost:8000/image-loader.php?path=${encodeURIComponent(imagePath)}`;
};

export function DetailScreen({ route, navigation }: Props): React.JSX.Element {
  const { articleId } = route.params;
  const cacheKey = `${ARTICLE_DETAIL_CACHE_PREFIX}${articleId}`;

  const [isLoading, setLoading] = useState(true);
  const [article, setArticle] = useState<ArticleDetails | null>(null);
  const [isOffline, setOffline] = useState(false);

  useEffect(() => {
    const getArticleDetails = async () => {
      // 1. Prova a caricare dalla cache
      try {
        const cachedArticle = await AsyncStorage.getItem(cacheKey);
        if (cachedArticle !== null) {
          const parsedArticle = JSON.parse(cachedArticle);
          setArticle(parsedArticle);
          navigation.setOptions({ title: parsedArticle.title });
        }
      } catch (e) {
        console.log('Errore nel leggere la cache del dettaglio:', e);
      }
      setLoading(false);

      // 2. Prova a caricare dalla rete
      try {
        const response = await fetch(`${API_BASE_URL}?id=${articleId}`);
        if (!response.ok) throw new Error(`Network response was not ok.`);

        const json = await response.json();
        if (!json.success) throw new Error(json.error || 'API returned an error');

        setArticle(json.data);
        setOffline(false);
        navigation.setOptions({ title: json.data.title });

        // 3. Salva nella cache
        await AsyncStorage.setItem(cacheKey, JSON.stringify(json.data));

      } catch (e) {
        console.log('Errore di rete, si utilizza il dettaglio in cache (se disponibile):', e);
        setOffline(true);
      }
    };

    getArticleDetails();
  }, [articleId, navigation, cacheKey]);

  if (isLoading) {
    return <ActivityIndicator size="large" color="#0000ff" style={styles.centered} />;
  }

  if (!article) {
    return (
      <View style={styles.centered}>
        <Text>Nessun dato da mostrare. Controlla la tua connessione.</Text>
      </View>
    );
  }

  return (
    <ScrollView style={styles.container}>
      {isOffline && (
        <View style={styles.offlineBanner}>
          <Text style={styles.offlineText}>Sei offline. Stai visualizzando contenuti non aggiornati.</Text>
        </View>
      )}
      {article.featured_image && (
        <Image
          source={{ uri: getImageUrl(article.featured_image) }}
          style={styles.image}
          resizeMode="cover"
        />
      )}
      <View style={styles.contentContainer}>
        <Text style={styles.title}>{article.title}</Text>
        <Text style={styles.subtitle}>{article.subtitle}</Text>
        <Text style={styles.content}>{article.content}</Text>
      </View>
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  centered: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    padding: 20,
  },
  container: {
    flex: 1,
    backgroundColor: '#fff',
  },
  offlineBanner: {
    backgroundColor: '#ffcc00',
    padding: 10,
    alignItems: 'center',
  },
  offlineText: {
    color: '#000',
  },
  image: {
    width: '100%',
    height: 250,
  },
  contentContainer: {
    padding: 20,
  },
  title: {
    fontSize: 24,
    fontWeight: 'bold',
    marginBottom: 8,
  },
  subtitle: {
    fontSize: 18,
    color: '#666',
    marginBottom: 16,
  },
  content: {
    fontSize: 16,
    lineHeight: 24,
  },
});
