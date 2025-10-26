// app/screens/DetailScreen.tsx

import React, { useEffect, useState } from 'react';
import { StyleSheet, Text, View, ScrollView, ActivityIndicator, Image } from 'react-native';
import type { NativeStackScreenProps } from '@react-navigation/native-stack';

// Definiamo i tipi per i parametri dello stack di navigazione
type RootStackParamList = {
  Home: undefined;
  Detail: { articleId: number };
};

// Definiamo un tipo per i dati dell'articolo che ci aspettiamo dall'API
interface ArticleDetails {
  id: number;
  title: string;
  subtitle: string;
  content: string;
  featured_image: string;
  // Aggiungi altri campi se necessario
}

type Props = NativeStackScreenProps<RootStackParamList, 'Detail'>;

const API_BASE_URL = 'http://localhost:8000/api/get_article_details.php';

// Funzione helper per costruire l'URL dell'immagine in modo sicuro
// Nota: Questo presuppone che `image-loader.php` sia nella root del server.
// Adatta il percorso se necessario.
const getImageUrl = (imagePath: string) => {
    if (!imagePath || imagePath.startsWith('http')) {
        return imagePath; // Se è già un URL completo o vuoto, restituiscilo
    }
    // Sostituisci 'localhost:8000' con il tuo dominio di produzione quando andrai live
    return `http://localhost:8000/image-loader.php?path=${encodeURIComponent(imagePath)}`;
};

export function DetailScreen({ route, navigation }: Props): React.JSX.Element {
  const { articleId } = route.params;

  const [isLoading, setLoading] = useState(true);
  const [article, setArticle] = useState<ArticleDetails | null>(null);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const getArticleDetails = async () => {
      try {
        const response = await fetch(`${API_BASE_URL}?id=${articleId}`);
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        const json = await response.json();
        if (!json.success) throw new Error(json.error || 'API returned an error');
        setArticle(json.data);
        // Aggiorna il titolo della schermata con il titolo dell'articolo
        navigation.setOptions({ title: json.data.title });
      } catch (e) {
        setError(e instanceof Error ? e.message : 'An unknown error occurred');
      } finally {
        setLoading(false);
      }
    };

    getArticleDetails();
  }, [articleId, navigation]);

  if (isLoading) {
    return <ActivityIndicator size="large" color="#0000ff" style={styles.centered} />;
  }

  if (error) {
    return (
      <View style={styles.centered}>
        <Text style={styles.errorText}>Errore nel caricamento: {error}</Text>
      </View>
    );
  }

  if (!article) {
    return (
      <View style={styles.centered}>
        <Text>Nessun articolo trovato.</Text>
      </View>
    );
  }

  return (
    <ScrollView style={styles.container}>
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
  },
  container: {
    flex: 1,
    backgroundColor: '#fff',
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
  errorText: {
    color: 'red',
  },
});
