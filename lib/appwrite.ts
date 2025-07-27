import { Client, Account, Databases, Storage, ID, Query } from 'appwrite';

const client = new Client()
    .setEndpoint(process.env.NEXT_PUBLIC_APPWRITE_ENDPOINT || 'https://cloud.appwrite.io/v1')
    .setProject(process.env.NEXT_PUBLIC_APPWRITE_PROJECT_ID || '');

export const account = new Account(client);
export const databases = new Databases(client);
export const storage = new Storage(client);

// Database and Collection IDs
export const DATABASE_ID = process.env.NEXT_PUBLIC_APPWRITE_DATABASE_ID || '';
export const USERS_COLLECTION_ID = process.env.NEXT_PUBLIC_APPWRITE_USERS_COLLECTION_ID || '';
export const MEDICAL_DOCS_COLLECTION_ID = process.env.NEXT_PUBLIC_APPWRITE_MEDICAL_DOCS_COLLECTION_ID || '';
export const APPOINTMENTS_COLLECTION_ID = process.env.NEXT_PUBLIC_APPWRITE_APPOINTMENTS_COLLECTION_ID || '';
export const HEALTH_RECORDS_COLLECTION_ID = process.env.NEXT_PUBLIC_APPWRITE_HEALTH_RECORDS_COLLECTION_ID || '';
export const PROFILE_BUCKET_ID = process.env.NEXT_PUBLIC_APPWRITE_PROFILE_BUCKET_ID || '';
export const MEDICAL_DOCS_BUCKET_ID = process.env.NEXT_PUBLIC_APPWRITE_MEDICAL_DOCS_BUCKET_ID || '';

// Auth functions
export const createAccount = async (email: string, password: string, name: string) => {
    try {
        const newAccount = await account.create(ID.unique(), email, password, name);
        return newAccount;
    } catch (error) {
        console.error('Error creating account:', error);
        throw error;
    }
};

export const signIn = async (email: string, password: string) => {
    try {
        const session = await account.createEmailPasswordSession(email, password);
        return session;
    } catch (error) {
        console.error('Error signing in:', error);
        throw error;
    }
};

export const signOut = async () => {
    try {
        await account.deleteSession('current');
    } catch (error) {
        console.error('Error signing out:', error);
        throw error;
    }
};

export const getCurrentUser = async () => {
    try {
        const user = await account.get();
        return user;
    } catch (error) {
        console.error('Error getting current user:', error);
        return null;
    }
};

// Profile functions
export const updateUserProfile = async (userId: string, data: any) => {
    try {
        return await databases.updateDocument(
            DATABASE_ID,
            USERS_COLLECTION_ID,
            userId,
            data
        );
    } catch (error) {
        console.error('Error updating user profile:', error);
        throw error;
    }
};

export const getUserProfile = async (userId: string) => {
    try {
        return await databases.getDocument(
            DATABASE_ID,
            USERS_COLLECTION_ID,
            userId
        );
    } catch (error) {
        console.error('Error getting user profile:', error);
        throw error;
    }
};

// Profile photo functions
export const uploadProfilePhoto = async (file: File) => {
    try {
        return await storage.createFile(PROFILE_BUCKET_ID, ID.unique(), file);
    } catch (error) {
        console.error('Error uploading profile photo:', error);
        throw error;
    }
};

export const updateUserProfilePhoto = async (imageUrl: string) => {
    try {
        return await account.updatePrefs({ imageUrl });
    } catch (error) {
        console.error('Error updating profile photo URL:', error);
        throw error;
    }
};

// Medical documents functions
export const uploadMedicalDocument = async (file: File, userId: string, title: string, description?: string) => {
    try {
        const uploadedFile = await storage.createFile(MEDICAL_DOCS_BUCKET_ID, ID.unique(), file);
        
        const document = await databases.createDocument(
            DATABASE_ID,
            MEDICAL_DOCS_COLLECTION_ID,
            ID.unique(),
            {
                userId,
                fileId: uploadedFile.$id,
                title,
                description: description || '',
                mimeType: file.type,
                size: file.size,
                url: `${process.env.NEXT_PUBLIC_APPWRITE_ENDPOINT}/storage/buckets/${MEDICAL_DOCS_BUCKET_ID}/files/${uploadedFile.$id}/view?project=${process.env.NEXT_PUBLIC_APPWRITE_PROJECT_ID}&mode=admin`,
                createdAt: new Date().toISOString()
            }
        );
        
        return document;
    } catch (error) {
        console.error('Error uploading medical document:', error);
        throw error;
    }
};

export const listMedicalDocuments = async () => {
    try {
        const user = await getCurrentUser();
        if (!user) throw new Error('User not authenticated');
        
        return await databases.listDocuments(
            DATABASE_ID,
            MEDICAL_DOCS_COLLECTION_ID,
            [Query.equal('userId', user.$id)]
        );
    } catch (error) {
        console.error('Error listing medical documents:', error);
        throw error;
    }
};

export const deleteMedicalDocument = async (documentId: string, fileId: string) => {
    try {
        await storage.deleteFile(MEDICAL_DOCS_BUCKET_ID, fileId);
        await databases.deleteDocument(DATABASE_ID, MEDICAL_DOCS_COLLECTION_ID, documentId);
    } catch (error) {
        console.error('Error deleting medical document:', error);
        throw error;
    }
};

// Appointments functions
export const createAppointment = async (appointmentData: any) => {
    try {
        const user = await getCurrentUser();
        if (!user) throw new Error('User not authenticated');
        
        return await databases.createDocument(
            DATABASE_ID,
            APPOINTMENTS_COLLECTION_ID,
            ID.unique(),
            {
                ...appointmentData,
                userId: user.$id,
                createdAt: new Date().toISOString()
            }
        );
    } catch (error) {
        console.error('Error creating appointment:', error);
        throw error;
    }
};

export const listAppointments = async () => {
    try {
        const user = await getCurrentUser();
        if (!user) throw new Error('User not authenticated');
        
        return await databases.listDocuments(
            DATABASE_ID,
            APPOINTMENTS_COLLECTION_ID,
            [Query.equal('userId', user.$id), Query.orderDesc('date')]
        );
    } catch (error) {
        console.error('Error listing appointments:', error);
        throw error;
    }
};

export const updateAppointment = async (appointmentId: string, data: any) => {
    try {
        return await databases.updateDocument(
            DATABASE_ID,
            APPOINTMENTS_COLLECTION_ID,
            appointmentId,
            data
        );
    } catch (error) {
        console.error('Error updating appointment:', error);
        throw error;
    }
};

export const deleteAppointment = async (appointmentId: string) => {
    try {
        return await databases.deleteDocument(
            DATABASE_ID,
            APPOINTMENTS_COLLECTION_ID,
            appointmentId
        );
    } catch (error) {
        console.error('Error deleting appointment:', error);
        throw error;
    }
};

// Health records functions
export const addHealthRecord = async (healthData: any) => {
    try {
        const user = await getCurrentUser();
        if (!user) throw new Error('User not authenticated');
        
        return await databases.createDocument(
            DATABASE_ID,
            HEALTH_RECORDS_COLLECTION_ID,
            ID.unique(),
            {
                ...healthData,
                userId: user.$id,
                recordedAt: new Date().toISOString()
            }
        );
    } catch (error) {
        console.error('Error adding health record:', error);
        throw error;
    }
};

export const getHealthRecords = async (type?: string, limit = 50) => {
    try {
        const user = await getCurrentUser();
        if (!user) throw new Error('User not authenticated');
        
        const queries = [Query.equal('userId', user.$id), Query.orderDesc('recordedAt'), Query.limit(limit)];
        if (type) {
            queries.push(Query.equal('type', type));
        }
        
        return await databases.listDocuments(
            DATABASE_ID,
            HEALTH_RECORDS_COLLECTION_ID,
            queries
        );
    } catch (error) {
        console.error('Error getting health records:', error);
        throw error;
    }
};

export const updateHealthRecord = async (recordId: string, data: any) => {
    try {
        return await databases.updateDocument(
            DATABASE_ID,
            HEALTH_RECORDS_COLLECTION_ID,
            recordId,
            data
        );
    } catch (error) {
        console.error('Error updating health record:', error);
        throw error;
    }
};

export const deleteHealthRecord = async (recordId: string) => {
    try {
        return await databases.deleteDocument(
            DATABASE_ID,
            HEALTH_RECORDS_COLLECTION_ID,
            recordId
        );
    } catch (error) {
        console.error('Error deleting health record:', error);
        throw error;
    }
};

// Emergency contacts functions
export const addEmergencyContact = async (contactData: any) => {
    try {
        const user = await getCurrentUser();
        if (!user) throw new Error('User not authenticated');
        
        return await account.updatePrefs({
            ...user.prefs,
            emergencyContacts: [
                ...(user.prefs?.emergencyContacts || []),
                {
                    ...contactData,
                    id: ID.unique(),
                    addedAt: new Date().toISOString()
                }
            ]
        });
    } catch (error) {
        console.error('Error adding emergency contact:', error);
        throw error;
    }
};

export const getEmergencyContacts = async () => {
    try {
        const user = await getCurrentUser();
        if (!user) throw new Error('User not authenticated');
        
        return user.prefs?.emergencyContacts || [];
    } catch (error) {
        console.error('Error getting emergency contacts:', error);
        throw error;
    }
};

// Utility functions
export const updateUserPreferences = async (preferences: any) => {
    try {
        const user = await getCurrentUser();
        if (!user) throw new Error('User not authenticated');
        
        return await account.updatePrefs({
            ...user.prefs,
            ...preferences
        });
    } catch (error) {
        console.error('Error updating user preferences:', error);
        throw error;
    }
};

export const getUserPreferences = async () => {
    try {
        const user = await getCurrentUser();
        if (!user) throw new Error('User not authenticated');
        
        return user.prefs || {};
    } catch (error) {
        console.error('Error getting user preferences:', error);
        throw error;
    }
};