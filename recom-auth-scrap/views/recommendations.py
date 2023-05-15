import nltk
from nltk.corpus import movie_reviews
from nltk.tokenize import word_tokenize
from nltk.stem import WordNetLemmatizer
from nltk.corpus import stopwords
import random

class Recommendation:
    def __init__(self):
        # download required NLTK data
        nltk.download('movie_reviews')
        nltk.download('punkt')
        nltk.download('stopwords')
        nltk.download('wordnet')

        # initialize lemmatizer and stopwords
        self.lemmatizer = WordNetLemmatizer()
        self.stop_words = set(stopwords.words('english'))

        # create a list of documents and their categories
        self.documents = [(list(movie_reviews.words(fileid)), category)
                          for category in movie_reviews.categories()
                          for fileid in movie_reviews.fileids(category)]

        # shuffle the documents
        random.shuffle(self.documents)

        # lemmatize and remove stop words from each document
        self.documents = [(self.lemmatize_words(doc), category) for doc, category in self.documents]

        # split the documents into training and testing sets
        self.train_set = self.documents[:1500]
        self.test_set = self.documents[1500:]

        # train the classifier
        self.classifier = nltk.NaiveBayesClassifier.train(self.train_set)

    def lemmatize_words(self, words):
        """
        Lemmatize and remove stop words from a list of words.
        """
        lemmatized_words = [self.lemmatizer.lemmatize(word.lower()) for word in words if word.isalpha()]
        return [word for word in lemmatized_words if word not in self.stop_words]

    def get_recommendation(self, text):
        """
        Get a movie recommendation based on a text input.
        """
        # tokenize and lemmatize the input text
        words = word_tokenize(text)
        words = self.lemmatize_words(words)

        # classify the input text using the trained classifier
        classification = self.classifier.classify(dict(nltk.FreqDist(words)))

        return classification
