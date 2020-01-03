import tensorflow
from tensorflow.keras.layers import Conv1D, Dense, Dropout, LSTM, Reshape, MaxPooling1D, Flatten
from tensorflow.keras.models import Sequential
import numpy as np
import glob
from collections import deque
from sklearn import preprocessing
import pandas as pd
def pct(current,future):
    if current<future:
        return 1
    return 0
raw_df =pd.read_csv(f'FB.csv')
df = pd.DataFrame()
df['close']=raw_df['Adj Close']
df['volume']=raw_df['Volume']
df['future']=df['close'].shift(-3)
df['target']=list(map(pct, df['close'], df['future']))
for col in df.columns:
    if col != 'future' and col != 'target':
        df[col] = df[col].pct_change()
df.dropna(inplace=True)
partial = deque(maxlen=20)
sequential_data=[]
for i,j,l in zip(df['close'],df['volume'],df['target']):
    if len(partial)==20:
        sequential_data.append(np.array([np.array(partial),[l]]))
    partial.append([i,j])
sequential_data =np.array(sequential_data)
print([i for i,_ in sequential_data][-1:])

X = np.array([i for i,_ in sequential_data])
y = np.array([i for _,i in sequential_data])


model = Sequential()

model.add(Conv1D(64, (3), activation='relu',input_shape=X.shape[1:]))
model.add(MaxPooling1D(pool_size=(2)))

model.add(Conv1D(128,(2),activation='relu'))
model.add(Dropout(0.2))
model.add(MaxPooling1D(pool_size=(2)))

model.add(Flatten())

model.add(Dense(128,activation='relu',input_shape=X.shape[1:]))

model.add(Reshape((1,model.output_shape[1])))

model.add(LSTM(64,return_sequences=True))
model.add(Dropout(0.2))
model.add(LSTM(32))
model.add(Dropout(0.2))
model.add(Dense(1,activation='softmax'))
model.compile(loss='mse',optimizer='adam',metrics=['accuracy'])
model.fit(X,y,batch_size=64,epochs=30)
