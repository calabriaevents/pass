// app/screens/UploadScreen.tsx

import React, { useState } from 'react';
import {
  StyleSheet,
  Text,
  View,
  TouchableOpacity,
  Image,
  TextInput,
  ScrollView,
  Alert,
  ActivityIndicator,
  Platform,
} from 'react-native';
import { launchImageLibrary } from 'react-native-image-picker';

const API_UPLOAD_URL = 'http://localhost:8000/api/upload-user-photo.php';

export function UploadScreen(): React.JSX.Element {
  const [image, setImage] = useState<any>(null);
  const [userName, setUserName] = useState('');
  const [userEmail, setUserEmail] = useState('');
  const [description, setDescription] = useState('');
  const [isUploading, setUploading] = useState(false);

  const selectImage = () => {
    launchImageLibrary({ mediaType: 'photo' }, (response) => {
      if (response.didCancel) {
        console.log('User cancelled image picker');
      } else if (response.errorCode) {
        console.log('ImagePicker Error: ', response.errorMessage);
      } else {
        if (response.assets && response.assets.length > 0) {
          setImage(response.assets[0]);
        }
      }
    });
  };

  const uploadImage = async () => {
    if (!image || !userName || !userEmail) {
      Alert.alert('Errore', 'Immagine, nome e email sono obbligatori.');
      return;
    }

    setUploading(true);

    const formData = new FormData();
    formData.append('photo', {
      uri: Platform.OS === 'android' ? image.uri : image.uri.replace('file://', ''),
      type: image.type,
      name: image.fileName,
    });
    formData.append('user_name', userName);
    formData.append('user_email', userEmail);
    formData.append('description', description);
    // Nota: article_id e province_id non sono gestiti in questa UI semplice
    // Potrebbero essere aggiunti con dei selettori se necessario
    formData.append('article_id', '1'); // Placeholder

    try {
      const response = await fetch(API_UPLOAD_URL, {
        method: 'POST',
        body: formData,
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      });

      const json = await response.json();

      if (json.success) {
        Alert.alert('Successo', 'Immagine caricata con successo! Sarà moderata a breve.');
        // Reset form
        setImage(null);
        setUserName('');
        setUserEmail('');
        setDescription('');
      } else {
        throw new Error(json.error || 'Errore sconosciuto durante l\'upload.');
      }

    } catch (error) {
      Alert.alert('Errore', `Si è verificato un problema: ${error instanceof Error ? error.message : 'Dettagli non disponibili'}`);
    } finally {
        setUploading(false);
    }
  };

  return (
    <ScrollView contentContainerStyle={styles.container}>
      <Text style={styles.header}>Carica la tua Foto</Text>

      <TouchableOpacity onPress={selectImage}>
        <View style={styles.imagePicker}>
          {image ? (
            <Image source={{ uri: image.uri }} style={styles.previewImage} />
          ) : (
            <Text>Seleziona un'immagine</Text>
          )}
        </View>
      </TouchableOpacity>

      <TextInput
        style={styles.input}
        placeholder="Il tuo nome"
        value={userName}
        onChangeText={setUserName}
      />
      <TextInput
        style={styles.input}
        placeholder="La tua email"
        value={userEmail}
        onChangeText={setUserEmail}
        keyboardType="email-address"
      />
      <TextInput
        style={[styles.input, styles.textArea]}
        placeholder="Descrizione (opzionale)"
        value={description}
        onChangeText={setDescription}
        multiline
      />

      <TouchableOpacity style={styles.button} onPress={uploadImage} disabled={isUploading}>
          {isUploading ? <ActivityIndicator color="#fff" /> : <Text style={styles.buttonText}>Invia</Text>}
      </TouchableOpacity>
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  container: {
    padding: 20,
    alignItems: 'center',
  },
  header: {
    fontSize: 22,
    fontWeight: 'bold',
    marginBottom: 20,
  },
  imagePicker: {
    width: 200,
    height: 200,
    backgroundColor: '#eee',
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 20,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: '#ccc',
  },
  previewImage: {
    width: '100%',
    height: '100%',
    borderRadius: 8,
  },
  input: {
    width: '100%',
    height: 40,
    borderColor: '#ccc',
    borderWidth: 1,
    borderRadius: 8,
    paddingHorizontal: 10,
    marginBottom: 10,
  },
  textArea: {
      height: 100,
      textAlignVertical: 'top',
  },
  button: {
    backgroundColor: '#D9232D',
    padding: 15,
    borderRadius: 8,
    alignItems: 'center',
    width: '100%',
  },
  buttonText: {
    color: '#fff',
    fontWeight: 'bold',
  },
});
